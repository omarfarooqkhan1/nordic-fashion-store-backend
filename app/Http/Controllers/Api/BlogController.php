<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class BlogController extends Controller
{
    /**
     * Get all published blogs
     */
    public function index(Request $request)
    {
        try {
            // Check if database is available
            try {
                Blog::count();
            } catch (\Exception $dbError) {
                // Database not available, return Nordflex mock data
                return $this->getMockBlogData();
            }

            $query = Blog::published()->orderBy('created_at', 'desc');

            // Search functionality
            if ($request->filled('search')) {
                $query->search($request->search);
            }

            // Filter by tags
            if ($request->filled('tags')) {
                $tags = is_array($request->tags) ? $request->tags : explode(',', $request->tags);
                $query->withTags($tags);
            }

            // Filter by author
            if ($request->filled('author')) {
                $query->where('author_name', 'like', '%' . $request->author . '%');
            }

            // Sorting options
            $sort = $request->get('sort', 'latest');
            switch ($sort) {
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'most_viewed':
                    $query->orderBy('views_count', 'desc');
                    break;
                case 'most_liked':
                    $query->orderBy('likes_count', 'desc');
                    break;
                case 'alphabetical':
                    $query->orderBy('title', 'asc');
                    break;
                default: // latest
                    $query->orderBy('created_at', 'desc');
                    break;
            }

            // Pagination
            $perPage = min($request->get('per_page', 12), 50); // Max 50 per page
            $blogs = $query->paginate($perPage);

            // Transform the data
            $transformedBlogs = $blogs->map(function ($blog) {
                return $this->transformBlogForList($blog);
            });
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
                ]
            ]);

        } catch (\Exception $e) { return response()->json([
                'message' => 'Failed to fetch blogs',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get a single blog post
     */
    public function show($slug)
    {
        try {
            $blog = Blog::published()->where('slug', $slug)->first();

            if (!$blog) {
                return response()->json([
                    'message' => 'Blog post not found'
                ], 404);
            }

            // Increment views count
            $blog->incrementViews();

            // Get related posts
            $relatedPosts = $blog->getRelatedBlogs(3)->map(function ($relatedBlog) {
                return $this->transformBlogForList($relatedBlog);
            });

            // Transform the data
            $transformedBlog = $this->transformBlogForDetail($blog);
            $transformedBlog['related_posts'] = $relatedPosts;
return response()->json($transformedBlog);

        } catch (\Exception $e) { return response()->json([
                'message' => 'Failed to fetch blog post',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get related blog posts
     */
    public function related($slug)
    {
        try {
            $currentBlog = Blog::published()->where('slug', $slug)->first();

            if (!$currentBlog) {
                return response()->json([
                    'message' => 'Blog post not found'
                ], 404);
            }

            $relatedBlogs = $currentBlog->getRelatedBlogs(4);

            // Transform the data
            $transformedBlogs = $relatedBlogs->map(function ($blog) {
                return $this->transformBlogForList($blog);
            });
return response()->json($transformedBlogs);

        } catch (\Exception $e) { return response()->json([
                'message' => 'Failed to fetch related blog posts',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get popular blog posts
     */
    public function popular(Request $request)
    {
        try {
            $limit = min($request->get('limit', 5), 20); // Max 20 popular posts
            
            // Cache popular posts for 30 minutes
            $popularBlogs = Cache::remember("popular_blogs_{$limit}", 30 * 60, function () use ($limit) {
                return Blog::published()->popular($limit)->get();
            });

            $transformedBlogs = $popularBlogs->map(function ($blog) {
                return $this->transformBlogForList($blog);
            });
return response()->json($transformedBlogs);

        } catch (\Exception $e) { return response()->json([
                'message' => 'Failed to fetch popular blog posts',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Like a blog post
     */
    public function like($slug)
    {
        try {
            $blog = Blog::published()->where('slug', $slug)->first();

            if (!$blog) {
                return response()->json([
                    'message' => 'Blog post not found'
                ], 404);
            }

            $blog->incrementLikes();
return response()->json([
                'message' => 'Blog post liked successfully',
                'likes_count' => $blog->fresh()->likes_count,
                'success' => true
            ]);

        } catch (\Exception $e) { return response()->json([
                'message' => 'Failed to like blog post',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Increment view count (separate endpoint)
     */
    public function view($slug)
    {
        try {
            $blog = Blog::published()->where('slug', $slug)->first();

            if (!$blog) {
                return response()->json([
                    'message' => 'Blog post not found'
                ], 404);
            }

            $blog->incrementViews();
return response()->json([
                'message' => 'View recorded successfully',
                'views_count' => $blog->fresh()->views_count,
                'success' => true
            ]);

        } catch (\Exception $e) { return response()->json([
                'message' => 'Failed to record view',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get all unique tags with statistics
     */
    public function tags()
    {
        try {
            // Check if database is available
            try {
                Blog::count();
            } catch (\Exception $dbError) {
                // Database not available, return Nordflex mock tags
                return response()->json([
                    'winter-jackets',
                    'finland',
                    'weather-resistant',
                    'leather-jackets',
                    'buying-guide',
                    'style-guide',
                    'fashion',
                    'comfort',
                    'timeless-fashion',
                    'leather-care',
                    'maintenance',
                    'cleaning',
                    'seasonal-fashion',
                    'versatility',
                    'sustainability',
                    'eco-friendly',
                    'craftsmanship',
                    'heritage',
                    'styling',
                    'brand-story'
                ]);
            }

            // Cache tags for 1 hour since they don't change frequently
            $tags = Cache::remember('blog_tags', 60 * 60, function () {
                return Blog::getAllTags();
            });

            sort($tags);
return response()->json($tags);

        } catch (\Exception $e) { return response()->json([
                'message' => 'Failed to fetch blog tags',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Search blogs
     */
    public function search(Request $request)
    {
        try {
            $searchTerm = $request->get('q', '');
            
            if (strlen($searchTerm) < 2) {
                return response()->json([
                    'data' => [],
                    'message' => 'Search term must be at least 2 characters'
                ]);
            }

            $blogs = Blog::published()
                ->search($searchTerm)
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();

            $transformedBlogs = $blogs->map(function ($blog) {
                return $this->transformBlogForList($blog);
            });
return response()->json([
                'data' => $transformedBlogs,
                'search_term' => $searchTerm,
                'total_results' => $transformedBlogs->count()
            ]);

        } catch (\Exception $e) { return response()->json([
                'message' => 'Failed to search blogs',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Transform blog data for list view
     */
    private function transformBlogForList($blog)
    {
        return [
            'id' => $blog->id,
            'title' => $blog->title,
            'slug' => $blog->slug,
            'excerpt' => $blog->excerpt,
            'featured_image' => $blog->featured_image,
            'author_name' => $blog->author_name,
            'tags' => $blog->tags ?? [],
            'views_count' => $blog->views_count,
            'likes_count' => $blog->likes_count,
            'published_at' => $blog->published_at,
            'formatted_published_date' => $blog->formatted_published_date,
            'reading_time' => $blog->reading_time,
            'created_at' => $blog->created_at,
        ];
    }

    /**
     * Transform blog data for detail view
     */
    private function transformBlogForDetail($blog)
    {
        return [
            'id' => $blog->id,
            'title' => $blog->title,
            'slug' => $blog->slug,
            'excerpt' => $blog->excerpt,
            'content' => $blog->content,
            'featured_image' => $blog->featured_image,
            'images' => $blog->images ?? [],
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
     * Get mock Nordflex blog data for when database is unavailable
     */
    private function getMockBlogData()
    {
        return response()->json([
            'data' => [
                [
                    'id' => 1,
                    'title' => 'Why Nordflex Leather Jackets are the Ultimate Choice for Finland\'s Harsh Winters',
                    'slug' => 'why-nordflex-leather-jackets-are-the-ultimate-choice-for-finlands-harsh-winters',
                    'excerpt' => 'Discover why Nordflex leather jackets are perfect for Finland\'s harsh winters with superior quality, weather-resistant properties, and timeless style.',
                    'featured_image' => '/storage/images/blogs/WhatsApp Image 2025-09-07 at 15.43.42.jpeg',
                    'author_name' => 'Nordflex Team',
                    'tags' => ['winter-jackets', 'finland', 'weather-resistant', 'leather-jackets'],
                    'views_count' => 1850,
                    'likes_count' => 142,
                    'published_at' => '2024-12-15T10:00:00Z',
                    'formatted_published_date' => 'Dec 15, 2024',
                    'reading_time' => '6 min read',
                    'created_at' => '2024-12-15T10:00:00Z',
                ],
                [
                    'id' => 2,
                    'title' => 'A Complete Guide to Choosing the Perfect Leather Jacket from Nordflex',
                    'slug' => 'a-complete-guide-to-choosing-the-perfect-leather-jacket-from-nordflex',
                    'excerpt' => 'Learn how to select the ideal Nordflex leather jacket with our comprehensive guide covering fit, style, materials, and features.',
                    'featured_image' => '/storage/images/blogs/WhatsApp Image 2025-09-07 at 15.43.43 (1).jpeg',
                    'author_name' => 'Style Expert',
                    'tags' => ['buying-guide', 'leather-jackets', 'style-guide', 'fashion'],
                    'views_count' => 2250,
                    'likes_count' => 189,
                    'published_at' => '2024-12-10T14:30:00Z',
                    'formatted_published_date' => 'Dec 10, 2024',
                    'reading_time' => '8 min read',
                    'created_at' => '2024-12-10T14:30:00Z',
                ],
                [
                    'id' => 3,
                    'title' => 'Sustainability in Fashion: How Nordflex is Revolutionizing Leather Jacket Production',
                    'slug' => 'sustainability-in-fashion-how-nordflex-is-revolutionizing-leather-jacket-production',
                    'excerpt' => 'Learn how Nordflex combines sustainable practices with premium craftsmanship to create eco-friendly leather jackets without compromising on quality.',
                    'featured_image' => '/storage/images/blogs/WhatsApp Image 2025-09-07 at 15.43.44.jpeg',
                    'author_name' => 'Sustainability Team',
                    'tags' => ['sustainability', 'eco-friendly', 'ethical-fashion', 'environment'],
                    'views_count' => 2100,
                    'likes_count' => 178,
                    'published_at' => '2024-12-05T09:15:00Z',
                    'formatted_published_date' => 'Dec 5, 2024',
                    'reading_time' => '7 min read',
                    'created_at' => '2024-12-05T09:15:00Z',
                ]
            ],
            'pagination' => [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 12,
                'total' => 3,
                'from' => 1,
                'to' => 3,
                'has_more_pages' => false,
            ]
        ]);
    }
}