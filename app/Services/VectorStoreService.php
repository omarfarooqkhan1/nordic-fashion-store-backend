<?php

namespace App\Services;

use HelgeSverre\Chromadb\Chromadb;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\Embeddings\GeminiEmbeddings;

class VectorStoreService
{
    private $client;
    private $collectionId;
    private $collectionName;
    private $embeddingFunction;

    public function __construct() {
        $this->client = new Chromadb(
            token: config('chromadb.api_key'),
            host: config('chromadb.host'),
            port: config('chromadb.port'),
            tenant: 'default_tenant',
            database: 'default_database'
        );
        
        $this->collectionName = config('chromadb.default_collection');
        
        // Initialize Gemini embedding function with the correct model
        $this->embeddingFunction = new GeminiEmbeddings(
            apiKey: config('gemini.api_key'),
            model: 'models/gemini-embedding-001' // Correct model name
        );
        
        // Ensure collection exists and get its ID
        $this->collectionId = $this->getOrCreateCollection();
    }

    /**
     * Get or create the collection and return its ID
     *
     * @return string
     */
    private function getOrCreateCollection(): string
    {
        try {
            // List all collections to find ours
            $response = $this->client->collections()->list();
            $collections = $response->json();
            
            // Check if our collection already exists
            foreach ($collections as $collection) {
                if ($collection['name'] === $this->collectionName) {
                    Log::info('Found existing collection: ' . $this->collectionName);
                    return $collection['id'];
                }
            }
            
            // Collection doesn't exist, create it
            Log::info('Creating new ChromaDB collection: ' . $this->collectionName);
            $createResponse = $this->client->collections()->create(
                name: $this->collectionName,
                getOrCreate: true
            );
            
            $createdCollection = $createResponse->json();
            return $createdCollection['id'];
        } catch (\Exception $e) {
            Log::error('Failed to get or create collection: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Initialize the knowledge base with all relevant information
     *
     * @return void
     */
    public function initializeKnowledgeBase()
    {
        Log::info('Initializing knowledge base');
        
        // Clear existing collection
        try {
            $this->client->collections()->delete($this->collectionId);
            $this->client->collections()->create(
                name: $this->collectionName,
                getOrCreate: true
            );
            // Re-get the collection ID after recreation
            $this->collectionId = $this->getOrCreateCollection();
        } catch (\Exception $e) {
            Log::warning('Failed to clear collection: ' . $e->getMessage());
        }
        
        // Add FAQ data
        try {
            $faqs = Http::get(config('app.url') . '/api/faqs')->json();
            
            if (is_array($faqs)) {
                // Handle the data wrapper in the API response
                $faqs = $faqs['data'] ?? $faqs;
                
                foreach ($faqs as $faq) {
                    $content = "Question: {$faq['question']}\nAnswer: {$faq['answer']}";
                    $metadata = [
                        'type' => 'faq',
                        'question' => $faq['question'],
                        'answer' => $faq['answer'],
                        'category' => $faq['category'] ?? 'general',
                        'id' => $faq['id']
                    ];
                    
                    $this->addDocument($content, $metadata, "faq_{$faq['id']}");
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to fetch FAQs for vector store: ' . $e->getMessage());
        }
        
        // Add blog data
        try {
            $blogs = Http::get(config('app.url') . '/api/blogs')->json();
            
            if (is_array($blogs)) {
                // Handle the data wrapper in the API response
                $blogs = $blogs['data'] ?? $blogs;
                
                foreach ($blogs as $blog) {
                    $content = "Title: {$blog['title']}\nExcerpt: {$blog['excerpt']}\nContent: {$blog['content']}";
                    $metadata = [
                        'type' => 'blog',
                        'title' => $blog['title'],
                        'excerpt' => $blog['excerpt'],
                        'slug' => $blog['slug'],
                        'url' => url("/blogs/{$blog['slug']}"),
                        'id' => $blog['id']
                    ];
                    
                    $this->addDocument($content, $metadata, "blog_{$blog['id']}");
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to fetch blogs for vector store: ' . $e->getMessage());
        }
        
        // Add about information
        $aboutContent = "Nord Flex is a premium apparel brand inspired by Viking resilience and craftsmanship. We specialize in rugged, high-performance leatherwear designed for modern warriors—whether you're conquering the outdoors or the urban grind. Our products feature Viking-inspired design with bold, minimalist aesthetics, premium materials like full-grain leather and reinforced stitching, built for durability, comfort, and timeless style, and ethically sourced and crafted with attention to detail.";
        $aboutMetadata = [
            'type' => 'about',
            'title' => 'About Nord Flex'
        ];
        
        $this->addDocument($aboutContent, $aboutMetadata, 'about_company');
        
        // Add contact information
        $contactContent = "You can reach us via email at support@nordflex.shop or by phone at +358 44 9782549. Our address is Nord Flex Co., Yliopistonkatu 25, 20100 Turku, Finland.";
        $contactMetadata = [
            'type' => 'contact',
            'title' => 'Contact Information'
        ];
        
        $this->addDocument($contactContent, $contactMetadata, 'contact_info');
        
        // Add terms and conditions
        $termsContent = "Welcome to NordFlex. These Terms and Conditions govern your use of our website, products, and services. By accessing or using our website, you agree to be bound by these Terms. If you do not agree to these Terms, please do not use our website or services.

Definitions:
- 'Company,' 'we,' 'us,' or 'our' refers to NordFlex Co.
- 'Website' refers to nordflex.shop and all associated pages
- 'Products' refers to leather goods, jackets, bags, wallets, and belts sold by NordFlex
- 'Services' refers to all services provided by NordFlex
- 'User,' 'you,' or 'your' refers to any person accessing or using our website or services

Intellectual Property Rights:
All content on this website, including but not limited to text, graphics, logos, images, audio clips, video clips, digital downloads, data compilations, and software, is the property of NordFlex or its content suppliers and is protected by international copyright laws. You may not reproduce, distribute, display, or transmit any content without our prior written permission.

Use of Website:
You may use our website for lawful purposes only. You agree not to:
- Use the website in any way that violates applicable laws or regulations
- Transmit or send unsolicited commercial communications
- Attempt to gain unauthorized access to any part of the website
- Use any automated system to access the website for any purpose
- Interfere with or disrupt the website or servers connected to the website

User Accounts:
If you create an account on our website, you are responsible for:
- Maintaining the confidentiality of your account and password
- All activities that occur under your account
- Providing accurate and complete information
- Notifying us immediately of any unauthorized use of your account

Product Information and Pricing:
We strive to provide accurate product descriptions and pricing. However:
- Product descriptions and images are for illustrative purposes only
- Prices are subject to change without notice
- We reserve the right to limit quantities and refuse orders
- All prices are in Euros (€) unless otherwise stated

Orders and Payment:
Order Process:
- All orders are subject to acceptance by NordFlex
- We reserve the right to refuse or cancel any order
- Payment must be received before order processing
- Accepted payment methods include credit cards, debit cards, and other methods as displayed
- All payments are processed securely through our payment partners

Shipping and Delivery:
Shipping Information:
- We ship worldwide from our facility in Finland
- Delivery times vary by location and shipping method selected
- Risk of loss and title pass to you upon delivery
- We are not responsible for delays caused by customs or other factors beyond our control
- Shipping costs are calculated at checkout and are non-refundable unless the order is cancelled by us

Returns and Exchanges:
Return Policy:
- Returns are accepted within 30 days of delivery
- Items must be in original condition with tags attached
- Custom or personalized items cannot be returned
- Return shipping costs are the responsibility of the customer unless the item is defective
- Refunds will be processed within 5-7 business days after receiving the returned item

Warranties and Disclaimers:
Product Warranties:
- We warrant that our products will be free from defects in materials and workmanship
- This warranty does not cover normal wear and tear, misuse, or damage from accidents
- Our liability is limited to the purchase price of the product
- We disclaim all other warranties, express or implied

Limitation of Liability:
To the extent permitted by law, NordFlex shall not be liable for indirect, incidental, special, consequential or punitive damages, including but not limited to loss of profits, data or use, arising from or relating to your use of our website or products.

Privacy Policy:
Your privacy is important to us. Please review our Privacy Policy, which also governs your use of our website, to understand our practices regarding collection and use of your personal information.

Modifications:
We reserve the right to modify these terms at any time. Changes are effective immediately upon posting on our website. Your continued use of our website after changes are posted constitutes acceptance of the modified terms.

Governing Law and Jurisdiction:
These terms are governed by and construed in accordance with the laws of Finland. Any disputes arising from these terms or your use of our website will be subject to the exclusive jurisdiction of the Finnish courts.

Contact Information:
If you have questions about these Terms and Conditions, please contact us at:
- Email: support@nordflex.shop
- Phone: +358 44 9782549
- Address: NordFlex Co., Yliopistonkatu 25, 20100 Turku, Finland";
        
        $termsMetadata = [
            'type' => 'terms',
            'title' => 'Terms and Conditions'
        ];
        
        $this->addDocument($termsContent, $termsMetadata, 'terms_conditions');
        
        // Add privacy policy
        $privacyContent = "NordFlex Co. ('we,' 'our,' or 'us') is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website nordflex.shop or use our services. Please read this privacy policy carefully. If you do not agree with the terms of this privacy policy, please do not access the site.

Information We Collect:
We may collect information about you in a variety of ways. The information we may collect via the Site includes:

Personal Data: Personally identifiable information that you voluntarily give to us when registering with the Site or when you choose to participate in various activities related to the Site, such as:
- Name and contact information (email address, phone number, mailing address)
- Account credentials (username and password)
- Payment information (credit card details, billing address)
- Order history and preferences

Derived Data: Information our servers automatically collect when you access the Site, such as:
- Your IP address
- Browser type and version
- Operating system
- Access times
- Pages viewed
- Referring website addresses

Use of Your Information:
Having accurate information about you permits us to provide you with a smooth, efficient, and customized experience. Specifically, we may use information collected about you via the Site to:
- Process transactions and send related information
- Send you administrative information
- Send you marketing and promotional communications
- Respond to your comments and questions
- Improve our website and services
- Monitor and analyze usage and trends
- Prevent fraudulent transactions and monitor against theft
- Comply with legal obligations

Disclosure of Your Information:
We may share information we have collected about you in certain situations. Your information may be disclosed as follows:

By Law or to Protect Rights: If we believe the release of information about you is necessary to respond to legal process, to investigate or remedy potential violations of our policies, or to protect the rights, property, and safety of others, we may share your information as permitted or required by any applicable law, rule, or regulation.

Business Transfers: We may share or transfer your information in connection with, or during negotiations of, any merger, sale of company assets, financing, or acquisition of all or a portion of our business to another company.

Third-Party Service Providers: We may share your information with third parties that perform services for us or on our behalf, including payment processing, data analysis, email delivery, hosting services, customer service, and marketing assistance.

Data Security:
We use administrative, technical, and physical security measures to help protect your personal information. While we have taken reasonable steps to secure the personal information you provide to us, please be aware that despite our efforts, no security measures are perfect or impenetrable, and no method of data transmission can be guaranteed against any interception or other type of misuse. Any information disclosed online is vulnerable to interception and misuse by unauthorized parties. Therefore, we cannot guarantee complete security if you provide personal information.

Cookies and Tracking Technologies:
We may use cookies, web beacons, tracking pixels, and other tracking technologies on the Site to help customize the Site and improve your experience. When you visit the Site, your browser may store cookies on your device. You may refuse to accept browser cookies by activating the appropriate setting on your browser. However, if you select this setting you may be unable to access certain parts of our Site.

Third-Party Websites:
The Site may contain links to third-party websites and applications of interest, including advertisements and external services, that are not affiliated with us. Once you have used these links to leave the Site, any information you provide to these third parties is not covered by this Privacy Policy, and we cannot guarantee the safety and privacy of your information.

Data Retention:
We will only keep your personal information for as long as it is necessary for the purposes set out in this privacy policy, unless a longer retention period is required or permitted by law. We will delete or anonymize your personal information when it is no longer needed for these purposes.

Your Rights:
Depending on your location, you may have certain rights regarding your personal information, including:
- The right to access your personal information
- The right to correct inaccurate personal information
- The right to delete your personal information
- The right to restrict or object to processing of your personal information
- The right to data portability
- The right to withdraw consent

To exercise any of these rights, please contact us using the information provided below.

Children's Privacy:
The Site is not intended for children under 13 years of age. We do not knowingly collect personal information from children under 13. If you are under 13, do not use or provide any information on this Site or through any of its features. If we learn we have collected or received personal information from a child under 13 without verification of parental consent, we will delete that information.

International Data Transfers:
Your information may be transferred to and processed in countries other than your own. These countries may have data protection laws that are different from the laws of your country. We will ensure that any such transfers are made in accordance with applicable data protection laws and that appropriate safeguards are in place to protect your personal information.

Changes to This Privacy Policy:
We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page and updating the 'Last updated' date. You are advised to review this Privacy Policy periodically for any changes. Changes to this Privacy Policy are effective when they are posted on this page.

Contact Us:
If you have questions or comments about this Privacy Policy, please contact us at:
- Email: support@nordflex.shop
- Phone: +358 44 9782549
- Address: NordFlex Co., Yliopistonkatu 25, 20100 Turku, Finland";
        
        $privacyMetadata = [
            'type' => 'privacy',
            'title' => 'Privacy Policy'
        ];
        
        $this->addDocument($privacyContent, $privacyMetadata, 'privacy_policy');
        
        Log::info('Knowledge base initialization completed');
    }

    /**
     * Add a document to the vector store
     *
     * @param string $content
     * @param array $metadata
     * @param string $id
     * @return void
     */
    public function addDocument(string $content, array $metadata = [], string $id = null)
    {
        $maxRetries = 3;
        $retryDelay = 5; // seconds
        
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                Log::info('Adding document (attempt ' . $attempt . '): ' . $id);
                Log::info('Content: ' . substr($content, 0, 100) . '...');
                Log::info('Metadata: ' . json_encode($metadata));
                
                // Generate embeddings using our custom Gemini embedding function
                $embeddings = $this->embeddingFunction->generate([$content]);
                
                // Add document with generated embeddings
                $response = $this->client->items()->add(
                    collectionId: $this->collectionId,
                    ids: [$id ?? uniqid()],
                    embeddings: $embeddings,
                    metadatas: [$metadata],
                    documents: [$content]
                );
                
                Log::info('Add response: ' . json_encode($response->json()));
                return; // Success, exit the retry loop
            } catch (\Exception $e) {
                Log::error('Failed to add document to vector store (attempt ' . $attempt . '): ' . $e->getMessage());
                
                // Check if it's a rate limit error and we have more retries
                if (strpos($e->getMessage(), 'quota') !== false || strpos($e->getMessage(), 'rate') !== false) {
                    if ($attempt < $maxRetries) {
                        Log::info('Rate limit hit, waiting ' . $retryDelay . ' seconds before retry...');
                        sleep($retryDelay);
                        $retryDelay *= 2; // Exponential backoff
                        continue;
                    }
                }
                
                // For non-rate limit errors or final attempt, log the full trace and re-throw
                Log::error('Trace: ' . $e->getTraceAsString());
                throw $e;
            }
        }
    }

    /**
     * Search for relevant documents based on a query
     *
     * @param string $query
     * @param int $limit
     * @return array
     */
    public function search(string $query, int $limit = 5): array
    {
        try {
            Log::info('Searching for: ' . $query);
            
            // Generate embedding for the query text using our custom embedding function
            $queryEmbeddings = $this->embeddingFunction->generate([$query]);
            
            // Use the regular query method with a default include parameter
            $response = $this->client->items()->query(
                collectionId: $this->collectionId,
                queryEmbeddings: $queryEmbeddings,
                nResults: $limit,
                include: ['documents', 'metadatas', 'distances'] // Specify what to include explicitly
            );
            
            $result = $response->json();
            Log::info('Search response: ' . json_encode($result));
            
            $documents = [];
            if (isset($result['documents'][0])) {
                for ($i = 0; $i < count($result['documents'][0]); $i++) {
                    $documents[] = [
                        'document' => $result['documents'][0][$i],
                        'metadata' => $result['metadatas'][0][$i] ?? [],
                        'distance' => $result['distances'][0][$i] ?? 0
                    ];
                }
            }
            
            return $documents;
        } catch (\Exception $e) {
            Log::warning('Failed to search vector store: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Format retrieved documents for the chatbot prompt
     *
     * @param array $documents
     * @return string
     */
    public function formatRetrievedDocuments(array $documents): string
    {
        if (empty($documents)) {
            return '';
        }

        $formatted = "\n\nRelevant Information:\n";
        
        foreach ($documents as $doc) {
            $metadata = $doc['metadata'];
            $distance = $doc['distance'];
            
            // Skip documents with high distance (low similarity)
            if ($distance > 1.0) {
                continue;
            }
            
            switch ($metadata['type'] ?? '') {
                case 'faq':
                    $formatted .= "\nQ: {$metadata['question']}\nA: {$metadata['answer']}\n";
                    break;
                    
                case 'blog':
                    $formatted .= "\nBlog: {$metadata['title']}\n{$metadata['excerpt']}\nURL: {$metadata['url']}\n";
                    break;
                    
                case 'about':
                    $formatted .= "\nAbout: {$metadata['title']}\n" . substr($doc['document'], 0, 200) . "...\n";
                    break;
                    
                case 'contact':
                    $formatted .= "\nContact: {$metadata['title']}\n" . $doc['document'] . "\n";
                    break;
                    
                case 'terms':
                    // Show more content for terms to include return policy information
                    $formatted .= "\nTerms and Conditions: {$metadata['title']}\n" . substr($doc['document'], 0, 1000) . "...\n";
                    break;
                    
                case 'privacy':
                    // Show more content for privacy policy
                    $formatted .= "\nPrivacy Policy: {$metadata['title']}\n" . substr($doc['document'], 0, 1000) . "...\n";
                    break;
                    
                default:
                    $formatted .= "\n" . substr($doc['document'], 0, 200) . "...\n";
                    break;
            }
        }
        
        return $formatted;
    }
}