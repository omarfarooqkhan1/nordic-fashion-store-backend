<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Services\LocalImageService;

class AdminBlogController extends Controller
{
    /**
     * Get all blogs (including drafts)
     */
    public function index(Request $request)
    {
        try {
            $query = Blog::orderBy('created_at', 'desc');

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Search functionality
            if ($request->filled('search')) {
                $query->search($request->search);
            }

            // Filter by author
            if ($request->filled('author')) {
                $query->where('author_name', 'like', '%' . $request->author . '%');
            }

            // Filter by tags
            if ($request->filled('tags')) {
                $tags = is_array($request->tags) ? $request->tags : explode(',', $request->tags);
                $query->withTags($tags);
            }

            // Date range filter
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            $allowedSortFields = ['created_at', 'title', 'views_count', 'likes_count', 'status'];
            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            // Pagination
            $perPage = min($request->get('per_page', 15), 100); // Max 100 per page for admin
            $blogs = $query->paginate($perPage);

            // Transform the data
            $transformedBlogs = $blogs->map(function ($blog) {
                return $this->transformBlogForAdmin($blog);
            });

            // Clear cache when blogs are updated
            $this->clearBlogCache();

            return response()->json([
                'data' => $transformedBlogs,
                'pagination' => [
                    'current_page' => $blogs->currentPage(),
                    'last_page' => $blogs->lastPage(),
                    'per_page' => $blogs->perPage(),
                    'total' => $blogs->total(),
                    'from' => $blogs->firstItem(),
                    'to' => $blogs->lastItem(),
                    'has_more_pages' => $blogs->hasMorePages(),
                ],
                'filters' => [
                    'status' => $request->status,
                    'search' => $request->search,
                    'author' => $request->author,
                    'tags' => $request->tags,
                    'date_from' => $request->date_from,
                    'date_to' => $request->date_to,
                    'sort_by' => $sortBy,
                    'sort_order' => $sortOrder,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch admin blogs', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to fetch blogs',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get a single blog post
     */
    public function show($id)
    {
        try {
            $blog = Blog::find($id);

            if (!$blog) {
                return response()->json([
                    'message' => 'Blog post not found'
                ], 404);
            }

            // Transform the data
            $transformedBlog = $this->transformBlogForAdminDetail($blog);

            return response()->json($transformedBlog);

        } catch (\Exception $e) {
            Log::error('Failed to fetch admin blog post', [
                'error' => $e->getMessage(),
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to fetch blog post',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Create a new blog post
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'excerpt' => 'nullable|string|max:1000',
                'content' => 'required|string',
                'featured_image' => 'nullable|string|max:500',
                'featured_image_file' => 'nullable|image|max:10240', // 10MB max
                'images' => 'nullable|array',
                'images.*' => 'nullable', // Allow both strings (existing images) and files (new uploads)
                'status' => 'required|in:draft,published,archived',
                'author_name' => 'required|string|max:255',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:500',
                'tags' => 'nullable|array',
                'tags.*' => 'string|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();
            
            // Handle featured image - file upload takes priority over URL
            if ($request->hasFile('featured_image_file')) {
                $localImageService = app(LocalImageService::class);
                $filename = Str::slug($data['title']) . '_featured_' . time() . '_' . uniqid();
                
                $result = $localImageService->uploadImage($request->file('featured_image_file'), 'blogs', $filename);
                
                if ($result) {
                    $data['featured_image'] = $result['secure_url'];
                    Log::info('Blog featured image uploaded successfully', [
                        'blog_title' => $data['title'],
                        'local_path' => $result['public_id'],
                        'compression_ratio' => $result['compression_ratio'] . '%'
                    ]);
                } else {
                    Log::error('Failed to upload blog featured image', [
                        'blog_title' => $data['title'],
                        'file' => $request->file('featured_image_file')->getClientOriginalName()
                    ]);
                    // If file upload fails, don't set featured_image
                    unset($data['featured_image']);
                }
            } elseif (!empty($data['featured_image'])) {
                // Only use URL if no file upload and URL is provided
                Log::info('Using featured image URL', [
                    'blog_title' => $data['title'],
                    'url' => $data['featured_image']
                ]);
            }
            
            // Handle gallery images - separate existing images from new file uploads
            $existingImages = [];
            $uploadedImages = [];
            
            if (isset($data['images']) && is_array($data['images'])) {
                foreach ($data['images'] as $image) {
                    if (is_string($image)) {
                        // This is an existing image URL
                        $existingImages[] = $image;
                    }
                }
            }
            
            // Handle new file uploads
            if ($request->hasFile('images')) {
                $localImageService = app(LocalImageService::class);
                
                foreach ($request->file('images') as $image) {
                    // Generate filename based on blog title
                    $filename = Str::slug($data['title']) . '_gallery_' . time() . '_' . uniqid();
                    
                    // Upload to local storage
                    $result = $localImageService->uploadImage($image, 'blogs', $filename);
                    
                    if ($result) {
                        $uploadedImages[] = $result['secure_url'];
                        Log::info('Blog gallery image uploaded successfully', [
                            'blog_title' => $data['title'],
                            'local_path' => $result['public_id'],
                            'compression_ratio' => $result['compression_ratio'] . '%'
                        ]);
                    } else {
                        Log::error('Failed to upload blog gallery image', [
                            'blog_title' => $data['title'],
                            'file' => $image->getClientOriginalName()
                        ]);
                    }
                }
            }
            
            // Merge existing images with newly uploaded ones
            $data['images'] = array_merge($existingImages, $uploadedImages);
            
            // Set featured_image to the first image if not explicitly set
            if (empty($data['featured_image']) && !empty($data['images'])) {
                $data['featured_image'] = $data['images'][0];
            }

            $blog = Blog::create($data);

            // Clear cache
            $this->clearBlogCache();

            Log::info('Blog post created', [
                'blog_id' => $blog->id,
                'title' => $blog->title,
                'status' => $blog->status
            ]);

            return response()->json([
                'message' => 'Blog post created successfully',
                'data' => $this->transformBlogForAdmin($blog),
                'success' => true
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to create blog post', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to create blog post',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Update a blog post
     */
    public function update(Request $request, $id)
    {
        try {
            $blog = Blog::find($id);

            if (!$blog) {
                return response()->json([
                    'message' => 'Blog post not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'excerpt' => 'nullable|string|max:1000',
                'content' => 'sometimes|required|string',
                'featured_image' => 'nullable|string|max:500',
                'featured_image_file' => 'nullable|image|max:10240', // 10MB max
                'images' => 'nullable|array',
                'images.*' => 'nullable', // Allow both strings (existing images) and files (new uploads)
                'status' => 'sometimes|required|in:draft,published,archived',
                'author_name' => 'sometimes|required|string|max:255',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:500',
                'tags' => 'nullable|array',
                'tags.*' => 'string|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();

            // Handle featured image - file upload takes priority over URL
            if ($request->hasFile('featured_image_file')) {
                $localImageService = app(LocalImageService::class);
                $filename = Str::slug($data['title'] ?? $blog->title) . '_featured_' . time() . '_' . uniqid();
                
                $result = $localImageService->uploadImage($request->file('featured_image_file'), 'blogs', $filename);
                
                if ($result) {
                    $data['featured_image'] = $result['secure_url'];
                    Log::info('Blog featured image uploaded successfully', [
                        'blog_id' => $blog->id,
                        'blog_title' => $data['title'] ?? $blog->title,
                        'local_path' => $result['public_id'],
                        'compression_ratio' => $result['compression_ratio'] . '%'
                    ]);
                } else {
                    Log::error('Failed to upload blog featured image', [
                        'blog_id' => $blog->id,
                        'file' => $request->file('featured_image_file')->getClientOriginalName()
                    ]);
                    // If file upload fails, don't set featured_image
                    unset($data['featured_image']);
                }
            } elseif (isset($data['featured_image']) && !empty($data['featured_image'])) {
                // Only use URL if no file upload and URL is provided
                Log::info('Using featured image URL', [
                    'blog_id' => $blog->id,
                    'blog_title' => $data['title'] ?? $blog->title,
                    'url' => $data['featured_image']
                ]);
            }

            // Handle gallery images - separate existing images from new file uploads
            $existingImages = [];
            $uploadedImages = [];
            
            if (isset($data['images']) && is_array($data['images'])) {
                foreach ($data['images'] as $image) {
                    if (is_string($image)) {
                        // This is an existing image URL
                        $existingImages[] = $image;
                    }
                }
            } elseif ($blog->images) {
                // If no images sent from frontend, keep existing ones
                $existingImages = $blog->images;
            }
            
            // Handle new file uploads
            if ($request->hasFile('images')) {
                $localImageService = app(LocalImageService::class);
                
                foreach ($request->file('images') as $image) {
                    // Generate filename based on blog title
                    $filename = Str::slug($data['title'] ?? $blog->title) . '_gallery_' . time() . '_' . uniqid();
                    
                    // Upload to local storage
                    $result = $localImageService->uploadImage($image, 'blogs', $filename);
                    
                    if ($result) {
                        $uploadedImages[] = $result['secure_url'];
                        Log::info('Blog gallery image uploaded successfully', [
                            'blog_id' => $blog->id,
                            'blog_title' => $data['title'] ?? $blog->title,
                            'local_path' => $result['public_id'],
                            'compression_ratio' => $result['compression_ratio'] . '%'
                        ]);
                    } else {
                        Log::error('Failed to upload blog gallery image', [
                            'blog_id' => $blog->id,
                            'file' => $image->getClientOriginalName()
                        ]);
                    }
                }
            }
            
            // Merge existing images with newly uploaded ones
            $data['images'] = array_merge($existingImages, $uploadedImages);
            
            // Set featured_image to the first image if not explicitly set
            if (empty($data['featured_image']) && !empty($data['images'])) {
                $data['featured_image'] = $data['images'][0];
            }

            $blog->update($data);

            // Clear cache
            $this->clearBlogCache();

            Log::info('Blog post updated', [
                'blog_id' => $blog->id,
                'title' => $blog->title,
                'status' => $blog->status
            ]);

            return response()->json([
                'message' => 'Blog post updated successfully',
                'data' => $this->transformBlogForAdmin($blog->fresh()),
                'success' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update blog post', [
                'error' => $e->getMessage(),
                'blog_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to update blog post',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Delete a blog post
     */
    public function destroy($id)
    {
        try {
            $blog = Blog::find($id);

            if (!$blog) {
                return response()->json([
                    'message' => 'Blog post not found'
                ], 404);
            }

            $blogTitle = $blog->title;
            $blog->delete();

            // Clear cache
            $this->clearBlogCache();

            Log::info('Blog post deleted', [
                'blog_id' => $id,
                'title' => $blogTitle
            ]);

            return response()->json([
                'message' => 'Blog post deleted successfully',
                'success' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete blog post', [
                'error' => $e->getMessage(),
                'blog_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to delete blog post',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get blog statistics
     */
    public function stats()
    {
        try {
            // Cache stats for 15 minutes
            $stats = Cache::remember('blog_stats', 15 * 60, function () {
                return [
                    'total_blogs' => Blog::count(),
                    'published_blogs' => Blog::published()->count(),
                    'draft_blogs' => Blog::draft()->count(),
                    'archived_blogs' => Blog::archived()->count(),
                    'total_views' => Blog::sum('views_count'),
                    'total_likes' => Blog::sum('likes_count'),
                    'avg_views_per_post' => round(Blog::avg('views_count'), 2),
                    'avg_likes_per_post' => round(Blog::avg('likes_count'), 2),
                    'recent_blogs' => Blog::recent(5)->get(['id', 'title', 'status', 'views_count', 'likes_count', 'created_at']),
                    'popular_blogs' => Blog::popular(5)->get(['id', 'title', 'views_count', 'created_at']),
                    'most_liked_blogs' => Blog::mostLiked(5)->get(['id', 'title', 'likes_count', 'created_at']),
                    'tag_stats' => Blog::getTagStats(),
                    'posts_by_status' => [
                        'published' => Blog::published()->count(),
                        'draft' => Blog::draft()->count(),
                        'archived' => Blog::archived()->count(),
                    ],
                    'monthly_posts' => Blog::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
                        ->groupBy('year', 'month')
                        ->orderBy('year', 'desc')
                        ->orderBy('month', 'desc')
                        ->limit(12)
                        ->get(),
                ];
            });

            return response()->json($stats);

        } catch (\Exception $e) {
            Log::error('Failed to fetch blog statistics', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to fetch blog statistics',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Bulk actions on multiple blog posts
     */
    public function bulkAction(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'action' => 'required|in:delete,publish,draft,archive',
                'blog_ids' => 'required|array|min:1',
                'blog_ids.*' => 'integer|exists:blogs,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $action = $request->action;
            $blogIds = $request->blog_ids;
            
            $blogs = Blog::whereIn('id', $blogIds)->get();
            
            if ($blogs->count() !== count($blogIds)) {
                return response()->json([
                    'message' => 'Some blog posts were not found'
                ], 404);
            }

            $affectedCount = 0;

            switch ($action) {
                case 'delete':
                    $affectedCount = Blog::whereIn('id', $blogIds)->delete();
                    break;
                case 'publish':
                    $affectedCount = Blog::whereIn('id', $blogIds)->update(['status' => 'published']);
                    break;
                case 'draft':
                    $affectedCount = Blog::whereIn('id', $blogIds)->update(['status' => 'draft']);
                    break;
                case 'archive':
                    $affectedCount = Blog::whereIn('id', $blogIds)->update(['status' => 'archived']);
                    break;
            }

            // Clear cache
            $this->clearBlogCache();

            Log::info('Bulk action performed on blog posts', [
                'action' => $action,
                'blog_ids' => $blogIds,
                'affected_count' => $affectedCount
            ]);

            return response()->json([
                'message' => "Successfully {$action}d {$affectedCount} blog post(s)",
                'affected_count' => $affectedCount,
                'success' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to perform bulk action on blog posts', [
                'error' => $e->getMessage(),
                'action' => $request->action ?? 'unknown',
                'blog_ids' => $request->blog_ids ?? [],
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to perform bulk action',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Transform blog data for admin list view
     */
    private function transformBlogForAdmin($blog)
    {
        return [
            'id' => $blog->id,
            'title' => $blog->title,
            'slug' => $blog->slug,
            'excerpt' => $blog->excerpt,
            'featured_image' => $blog->featured_image,
            'status' => $blog->status,
            'author_name' => $blog->author_name,
            'tags' => $blog->tags ?? [],
            'views_count' => $blog->views_count,
            'likes_count' => $blog->likes_count,
            'word_count' => $blog->word_count,
            'published_at' => $blog->published_at,
            'formatted_published_date' => $blog->formatted_published_date,
            'reading_time' => $blog->reading_time,
            'created_at' => $blog->created_at,
            'updated_at' => $blog->updated_at,
        ];
    }

    /**
     * Transform blog data for admin detail view
     */
    private function transformBlogForAdminDetail($blog)
    {
        return [
            'id' => $blog->id,
            'title' => $blog->title,
            'slug' => $blog->slug,
            'excerpt' => $blog->excerpt,
            'content' => $blog->content,
            'featured_image' => $blog->featured_image,
            'images' => $blog->images ?? [],
            'status' => $blog->status,
            'author_name' => $blog->author_name,
            'meta_title' => $blog->meta_title,
            'meta_description' => $blog->meta_description,
            'seo_title' => $blog->seo_title,
            'seo_description' => $blog->seo_description,
            'tags' => $blog->tags ?? [],
            'views_count' => $blog->views_count,
            'likes_count' => $blog->likes_count,
            'word_count' => $blog->word_count,
            'published_at' => $blog->published_at,
            'formatted_published_date' => $blog->formatted_published_date,
            'reading_time' => $blog->reading_time,
            'created_at' => $blog->created_at,
            'updated_at' => $blog->updated_at,
        ];
    }

    /**
     * Clear blog-related cache
     */
    private function clearBlogCache()
    {
        Cache::forget('blog_tags');
        Cache::forget('blog_stats');
        
        // Clear popular blogs cache for different limits
        for ($i = 1; $i <= 20; $i++) {
            Cache::forget("popular_blogs_{$i}");
        }
        
        Log::info('Blog cache cleared');
    }
}