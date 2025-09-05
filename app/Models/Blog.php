<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'images',
        'status',
        'author_name',
        'meta_title',
        'meta_description',
        'tags',
        'views_count',
        'likes_count',
    ];

    protected $casts = [
        'tags' => 'array',
        'images' => 'array',
        'views_count' => 'integer',
        'likes_count' => 'integer',
    ];

    protected $appends = [
        'published_at',
        'formatted_published_date',
        'reading_time',
        'excerpt_plain_text'
    ];

    /**
     * Get the featured image with fallback
     */
    public function getFeaturedImageAttribute($value)
    {
        // If featured_image is set directly, use it
        if ($value) {
            return $value;
        }
        
        // Otherwise, use the first image from the images array
        $images = $this->attributes['images'] ? json_decode($this->attributes['images'], true) : [];
        return !empty($images) ? $images[0] : null;
    }

    /**
     * Get the published date (use created_at for published blogs)
     */
    public function getPublishedAtAttribute()
    {
        return $this->status === 'published' ? $this->created_at : null;
    }

    /**
     * Get formatted published date
     */
    public function getFormattedPublishedDateAttribute()
    {
        return $this->published_at ? $this->published_at->format('M j, Y') : null;
    }

    /**
     * Get reading time estimate
     */
    public function getReadingTimeAttribute()
    {
        $content = $this->content ?? '';
        $wordCount = str_word_count(strip_tags($content));
        $minutes = ceil($wordCount / 200); // Average reading speed: 200 words per minute
        return $minutes . ' min read';
    }

    /**
     * Get plain text excerpt without HTML
     */
    public function getExcerptPlainTextAttribute()
    {
        return strip_tags($this->excerpt ?? '');
    }

    /**
     * Get SEO-optimized meta title
     */
    public function getSeoTitleAttribute()
    {
        return $this->meta_title ?: $this->title . ' - Nordflex Blog';
    }

    /**
     * Get SEO-optimized meta description
     */
    public function getSeoDescriptionAttribute()
    {
        if ($this->meta_description) {
            return $this->meta_description;
        }
        
        // Fallback to excerpt or truncated content
        $description = $this->excerpt ?: strip_tags($this->content ?? '');
        return Str::limit($description, 155);
    }

    /**
     * Get content word count
     */
    public function getWordCountAttribute()
    {
        return str_word_count(strip_tags($this->content ?? ''));
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($blog) {
            if (empty($blog->slug)) {
                $blog->slug = static::generateUniqueSlug($blog->title);
            }
        });

        static::updating(function ($blog) {
            if ($blog->isDirty('title') && empty($blog->slug)) {
                $blog->slug = static::generateUniqueSlug($blog->title, $blog->id);
            }
        });
    }

    /**
     * Generate unique slug
     */
    protected static function generateUniqueSlug($title, $excludeId = null)
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        while (static::slugExists($slug, $excludeId)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if slug exists
     */
    protected static function slugExists($slug, $excludeId = null)
    {
        $query = static::where('slug', $slug);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Scope for published blogs
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope for draft blogs
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope for archived blogs
     */
    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    /**
     * Scope for searching blogs
     */
    public function scopeSearch($query, $searchTerm)
    {
        return $query->where(function ($q) use ($searchTerm) {
            $q->where('title', 'like', "%{$searchTerm}%")
              ->orWhere('excerpt', 'like', "%{$searchTerm}%")
              ->orWhere('content', 'like', "%{$searchTerm}%")
              ->orWhere('author_name', 'like', "%{$searchTerm}%");
        });
    }

    /**
     * Scope for filtering by tags
     */
    public function scopeWithTags($query, $tags)
    {
        if (empty($tags)) {
            return $query;
        }

        $tags = is_array($tags) ? $tags : [$tags];
        
        return $query->where(function ($q) use ($tags) {
            foreach ($tags as $tag) {
                $q->orWhereJsonContains('tags', trim($tag));
            }
        });
    }

    /**
     * Scope for popular blogs (by views)
     */
    public function scopePopular($query, $limit = 5)
    {
        return $query->orderBy('views_count', 'desc')->limit($limit);
    }

    /**
     * Scope for most liked blogs
     */
    public function scopeMostLiked($query, $limit = 5)
    {
        return $query->orderBy('likes_count', 'desc')->limit($limit);
    }

    /**
     * Scope for recent blogs
     */
    public function scopeRecent($query, $limit = 5)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    /**
     * Get the route key for the model
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Increment views count
     */
    public function incrementViews()
    {
        $this->increment('views_count');
        return $this;
    }

    /**
     * Increment likes count
     */
    public function incrementLikes()
    {
        $this->increment('likes_count');
        return $this;
    }

    /**
     * Get related blogs based on tags
     */
    public function getRelatedBlogs($limit = 3)
    {
        if (empty($this->tags)) {
            return static::published()
                ->where('id', '!=', $this->id)
                ->recent($limit)
                ->get();
        }

        return static::published()
            ->where('id', '!=', $this->id)
            ->withTags($this->tags)
            ->recent($limit)
            ->get();
    }

    /**
     * Get all unique tags from published blogs
     */
    public static function getAllTags()
    {
        $blogs = static::published()->whereNotNull('tags')->get();
        $allTags = [];

        foreach ($blogs as $blog) {
            if ($blog->tags) {
                $allTags = array_merge($allTags, $blog->tags);
            }
        }

        return array_unique($allTags);
    }

    /**
     * Get tag statistics
     */
    public static function getTagStats()
    {
        $blogs = static::published()->whereNotNull('tags')->get();
        $tagStats = [];

        foreach ($blogs as $blog) {
            if ($blog->tags) {
                foreach ($blog->tags as $tag) {
                    $tagStats[$tag] = ($tagStats[$tag] ?? 0) + 1;
                }
            }
        }

        arsort($tagStats);
        return $tagStats;
    }

    /**
     * Check if blog is published
     */
    public function isPublished()
    {
        return $this->status === 'published';
    }

    /**
     * Check if blog is draft
     */
    public function isDraft()
    {
        return $this->status === 'draft';
    }

    /**
     * Check if blog is archived
     */
    public function isArchived()
    {
        return $this->status === 'archived';
    }
}