<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StaticPage;

class StaticPageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'slug' => 'terms-and-conditions',
                'title' => 'Terms and Conditions',
                'content' => $this->getTermsContent(),
            ],
            [
                'slug' => 'privacy-policy',
                'title' => 'Privacy Policy',
                'content' => $this->getPrivacyContent(),
            ],
            [
                'slug' => 'delivery',
                'title' => 'Delivery Information',
                'content' => $this->getDeliveryContent(),
            ],
            [
                'slug' => 'return-policy',
                'title' => 'Return Policy',
                'content' => $this->getReturnPolicyContent(),
            ],
            [
                'slug' => 'product-care-guide',
                'title' => 'Product Care Guide',
                'content' => $this->getProductCareContent(),
            ],
        ];

        foreach ($pages as $page) {
            StaticPage::updateOrCreate(
                ['slug' => $page['slug']],
                $page
            );
        }
    }

    private function getTermsContent(): string
    {
        return <<<'HTML'
<div class="space-y-6">
    <section>
        <h2 class="text-2xl font-bold mb-4">1. Introduction</h2>
        <p>Welcome to Nordflex. These Terms and Conditions govern your use of our website and the purchase of our products. By accessing our website or making a purchase, you agree to be bound by these terms.</p>
    </section>

    <section>
        <h2 class="text-2xl font-bold mb-4">2. Definitions</h2>
        <p>"We," "us," and "our" refer to Nordflex. "You" and "your" refer to the user or customer. "Products" refer to the leather goods and accessories sold on our website.</p>
    </section>

    <section>
        <h2 class="text-2xl font-bold mb-4">3. Intellectual Property</h2>
        <p>All content on this website, including text, graphics, logos, images, and software, is the property of Nordflex and protected by international copyright laws. You may not reproduce, distribute, or create derivative works without our express written permission.</p>
    </section>

    <section>
        <h2 class="text-2xl font-bold mb-4">4. Use of Website</h2>
        <p>You agree to use our website only for lawful purposes. You must not use our website in any way that causes damage to the website or impairs its availability or accessibility.</p>
    </section>

    <section>
        <h2 class="text-2xl font-bold mb-4">5. User Accounts</h2>
        <p>When you create an account, you must provide accurate and complete information. You are responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account.</p>
    </section>

    <section>
        <h2 class="text-2xl font-bold mb-4">6. Product Information</h2>
        <p>We strive to provide accurate product descriptions and images. However, we do not warrant that product descriptions, colors, or other content are accurate, complete, or error-free.</p>
    </section>

    <section>
        <h2 class="text-2xl font-bold mb-4">7. Orders and Payment</h2>
        <p>All orders are subject to acceptance and availability. We reserve the right to refuse or cancel any order. Prices are subject to change without notice. Payment must be received before we dispatch your order.</p>
    </section>

    <section>
        <h2 class="text-2xl font-bold mb-4">8. Shipping and Delivery</h2>
        <p>We aim to dispatch orders within 1-2 business days. Delivery times vary by location. Risk of loss and title for products pass to you upon delivery to the carrier.</p>
    </section>

    <section>
        <h2 class="text-2xl font-bold mb-4">9. Returns and Exchanges</h2>
        <p>We accept returns within 30 days of delivery for items in original condition with tags attached. Custom-made items are non-returnable. Return shipping costs are the responsibility of the customer unless the return is due to our error.</p>
    </section>

    <section>
        <h2 class="text-2xl font-bold mb-4">10. Warranties</h2>
        <p>Our products are warranted to be free from defects in materials and workmanship under normal use for a period of one year from the date of purchase. This warranty does not cover damage from misuse, accidents, or normal wear and tear.</p>
    </section>

    <section>
        <h2 class="text-2xl font-bold mb-4">11. Limitation of Liability</h2>
        <p>To the fullest extent permitted by law, Nordflex shall not be liable for any indirect, incidental, special, consequential, or punitive damages arising out of or relating to your use of our website or products.</p>
    </section>

    <section>
        <h2 class="text-2xl font-bold mb-4">12. Privacy</h2>
        <p>Your use of our website is also governed by our Privacy Policy. Please review our Privacy Policy to understand our practices.</p>
    </section>

    <section>
        <h2 class="text-2xl font-bold mb-4">13. Modifications</h2>
        <p>We reserve the right to modify these Terms and Conditions at any time. Changes will be effective immediately upon posting to the website. Your continued use of the website after changes constitutes acceptance of the modified terms.</p>
    </section>

    <section>
        <h2 class="text-2xl font-bold mb-4">14. Governing Law</h2>
        <p>These Terms and Conditions are governed by and construed in accordance with the laws of Finland, without regard to its conflict of law provisions.</p>
    </section>

    <section>
        <h2 class="text-2xl font-bold mb-4">15. Contact Information</h2>
        <p>If you have any questions about these Terms and Conditions, please contact us at <a href="mailto:support@nordflex.store" class="text-gold-600 hover:text-gold-700">support@nordflex.store</a></p>
    </section>
</div>
HTML;
    }

    private function getPrivacyContent(): string
    {
        return <<<'HTML'
<div class="space-y-6">
    <section>
        <h2 class="text-2xl font-bold mb-4">1. Introduction</h2>
        <p>Nordflex ("we," "us," or "our") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website or make a purchase.</p>
    </section>

    <section>
        <h2 class="text-2xl font-bold mb-4">2. Information We Collect</h2>
        <p>We collect information that you provide directly to us, such as when you create an account, make a purchase, subscribe to our newsletter, or contact us. This may include your name, email address, postal address, phone number, and payment information.</p>
    </section>

    <section>
        <h2 class="text-2xl font-bold mb-4">3. How We Use Your Information</h2>
        <p>We use the information we collect to process your orders, communicate with you, improve our website and services, send you marketing communications (with your consent), and comply with legal obligations.</p>
    </section>

    <section>
        <h2 class="text-2xl font-bold mb-4">4. Information Sharing</h2>
        <p>We do not sell your personal information. We may share your information with service providers who assist us in operating our website and conducting our business, subject to confidentiality obligations.</p>
    </section>

    <section>
        <h2 class="text-2xl font-bold mb-4">5. Data Security</h2>
        <p>We implement appropriate technical and organizational measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction.</p>
    </section>

    <section>
        <h2 class="text-2xl font-bold mb-4">6. Cookies and Tracking Technologies</h2>
        <p>We use cookies and similar tracking technologies to enhance your browsing experience, analyze website traffic, and personalize content. You can control cookies through your browser settings.</p>
    </section>

    <section>
        <h2 class="text-2xl font-bold mb-4">7. Third-Party Websites</h2>
        <p>Our website may contain links to third-party websites. We are not responsible for the privacy practices of these websites. We encourage you to read their privacy policies.</p>
    </section>

    <section>
        <h2 class="text-2xl font-bold mb-4">8. Data Retention</h2>
        <p>We retain your personal information for as long as necessary to fulfill the purposes outlined in this Privacy Policy, unless a longer retention period is required by law.</p>
    </section>

    <section>
        <h2 class="text-2xl font-bold mb-4">9. Your Rights</h2>
        <p>You have the right to access, correct, or delete your personal information. You may also object to or restrict certain processing of your data. To exercise these rights, please contact us at <a href="mailto:support@nordflex.store" class="text-gold-600 hover:text-gold-700">support@nordflex.store</a></p>
    </section>

    <section>
        <h2 class="text-2xl font-bold mb-4">10. Children's Privacy</h2>
        <p>Our website is not intended for children under 16 years of age. We do not knowingly collect personal information from children under 16.</p>
    </section>

    <section>
        <h2 class="text-2xl font-bold mb-4">11. International Data Transfers</h2>
        <p>Your information may be transferred to and processed in countries other than your country of residence. We ensure appropriate safeguards are in place to protect your information.</p>
    </section>

    <section>
        <h2 class="text-2xl font-bold mb-4">12. Changes to This Policy</h2>
        <p>We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new policy on this page and updating the "Last Updated" date.</p>
    </section>

    <section>
        <h2 class="text-2xl font-bold mb-4">13. Contact Us</h2>
        <p>If you have any questions about this Privacy Policy, please contact us at <a href="mailto:support@nordflex.store" class="text-gold-600 hover:text-gold-700">support@nordflex.store</a></p>
    </section>
</div>
HTML;
    }

    private function getDeliveryContent(): string
    {
        return <<<'HTML'
<div class="space-y-6">
    <section>
        <h2 class="text-2xl font-bold mb-4">Shipping Options</h2>
        
        <div class="space-y-4">
            <div class="border-l-4 border-gold-500 pl-4">
                <h3 class="text-xl font-semibold mb-2">Standard Delivery (Free)</h3>
                <p class="mb-2"><strong>5-7 business days</strong></p>
                <p>Free standard shipping on all orders. Your items will be carefully packaged and delivered to your doorstep.</p>
            </div>

            <div class="border-l-4 border-blue-500 pl-4">
                <h3 class="text-xl font-semibold mb-2">Express Delivery</h3>
                <p class="mb-2"><strong>2-3 business days</strong></p>
                <p>Need your order faster? Choose express delivery at checkout for expedited shipping.</p>
            </div>
        </div>
    </section>

    <section>
        <h2 class="text-2xl font-bold mb-4">Delivery Process</h2>
        
        <div class="space-y-4">
            <div>
                <h3 class="text-lg font-semibold mb-2">1. Order Confirmation</h3>
                <p>Once your order is placed, you'll receive an email confirmation with your order details and estimated delivery date.</p>
            </div>

            <div>
                <h3 class="text-lg font-semibold mb-2">2. Processing</h3>
                <p>Our team carefully inspects and packages your items. This typically takes 1-2 business days.</p>
            </div>

            <div>
                <h3 class="text-lg font-semibold mb-2">3. Shipping</h3>
                <p>Your order is dispatched with our trusted courier partners. You'll receive a tracking number to monitor your delivery.</p>
            </div>

            <div>
                <h3 class="text-lg font-semibold mb-2">4. Delivery</h3>
                <p>Your package arrives at your doorstep. Sign for your delivery and enjoy your new Nordflex products!</p>
            </div>
        </div>
    </section>

    <section>
        <h2 class="text-2xl font-bold mb-4">International Shipping</h2>
        <p class="mb-4">We ship to most countries worldwide. International delivery times vary by destination:</p>
        
        <ul class="space-y-2">
            <li><strong>Europe:</strong> 5-10 business days</li>
            <li><strong>North America:</strong> 7-14 business days</li>
            <li><strong>Asia & Pacific:</strong> 10-15 business days</li>
            <li><strong>Rest of World:</strong> 10-20 business days</li>
        </ul>

        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mt-4">
            <p><strong>Note:</strong> International orders may be subject to customs duties and taxes, which are the responsibility of the recipient.</p>
        </div>
    </section>

    <section>
        <h2 class="text-2xl font-bold mb-4">Track Your Order</h2>
        <p class="mb-4">Once your order has been shipped, you'll receive a tracking number via email. You can use this number to track your package's journey to your doorstep.</p>
        <p>For any delivery-related questions, please contact our customer service team at <a href="mailto:support@nordflex.store" class="text-gold-600 hover:text-gold-700">support@nordflex.store</a></p>
    </section>
</div>
HTML;
    }

    private function getReturnPolicyContent(): string
    {
        return <<<'HTML'
<div class="space-y-6">
    <section>
        <h2 class="text-2xl font-bold mb-4">30-Day Return Window</h2>
        <p class="mb-4">We offer a 30-day return policy from the date of delivery. If you're not completely satisfied with your purchase, you can return it for a full refund or exchange.</p>
        
        <div class="bg-gold-50 dark:bg-gold-900/20 border border-gold-200 dark:border-gold-800 rounded-lg p-4">
            <p><strong>Important:</strong> Items must be returned in their original condition with all tags attached and in the original packaging.</p>
        </div>
    </section>

    <section>
        <h2 class="text-2xl font-bold mb-4">Eligible for Return</h2>
        <ul class="space-y-2">
            <li>✓ Items in original, unworn condition with all tags attached</li>
            <li>✓ Products in original packaging with all accessories included</li>
            <li>✓ Items without any signs of wear, damage, or alterations</li>
            <li>✓ Products with proof of purchase (order confirmation or receipt)</li>
        </ul>
    </section>

    <section>
        <h2 class="text-2xl font-bold mb-4">Non-Returnable Items</h2>
        <ul class="space-y-2">
            <li>✗ Custom-made or personalized items (including custom jackets)</li>
            <li>✗ Items marked as final sale or clearance</li>
            <li>✗ Products that have been worn, used, or damaged</li>
            <li>✗ Items without original tags or packaging</li>
        </ul>
    </section>

    <section>
        <h2 class="text-2xl font-bold mb-4">How to Return</h2>
        
        <div class="space-y-4">
            <div>
                <h3 class="text-lg font-semibold mb-2">1. Contact Us</h3>
                <p>Email us at <a href="mailto:support@nordflex.store" class="text-gold-600 hover:text-gold-700">support@nordflex.store</a> with your order number and reason for return.</p>
            </div>

            <div>
                <h3 class="text-lg font-semibold mb-2">2. Receive Authorization</h3>
                <p>We'll send you a Return Authorization (RA) number and return shipping instructions within 24 hours.</p>
            </div>

            <div>
                <h3 class="text-lg font-semibold mb-2">3. Pack Your Item</h3>
                <p>Securely pack the item in its original packaging with all tags and accessories. Include the RA number.</p>
            </div>

            <div>
                <h3 class="text-lg font-semibold mb-2">4. Ship It Back</h3>
                <p>Send the package using a trackable shipping method. Keep your tracking number for reference.</p>
            </div>

            <div>
                <h3 class="text-lg font-semibold mb-2">5. Receive Your Refund</h3>
                <p>Once we receive and inspect your return, we'll process your refund within 5-7 business days.</p>
            </div>
        </div>
    </section>

    <section>
        <h2 class="text-2xl font-bold mb-4">Refund Information</h2>
        <p class="mb-4">Refunds will be issued to the original payment method used for the purchase. Please allow 5-10 business days for the refund to appear in your account after we process it.</p>
        
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <p><strong>Shipping Costs:</strong> Original shipping costs are non-refundable unless the return is due to our error or a defective product.</p>
        </div>
    </section>

    <section>
        <h2 class="text-2xl font-bold mb-4">Exchanges</h2>
        <p class="mb-4">If you'd like to exchange an item for a different size or color, please follow the return process above and place a new order for the desired item. This ensures you receive your new item as quickly as possible.</p>
        <p>For any questions about our return policy, please contact us at <a href="mailto:support@nordflex.store" class="text-gold-600 hover:text-gold-700">support@nordflex.store</a></p>
    </section>
</div>
HTML;
    }

    private function getProductCareContent(): string
    {
        return <<<'HTML'
<div class="space-y-6">
    <section>
        <p class="text-lg leading-relaxed">Proper care of your leather products is an investment in their longevity. With the right techniques and products, your Nordflex leather items can last for decades while maintaining their beauty and functionality.</p>
    </section>

    <section>
        <h2 class="text-2xl font-bold mb-4">Understanding Leather Types</h2>
        <p class="mb-4">Different types of leather require different care approaches. Understanding your product's leather type is the first step in proper maintenance.</p>

        <div class="space-y-4">
            <div class="border-l-4 border-gold-500 pl-4">
                <h3 class="text-lg font-semibold mb-2">Full-Grain Leather</h3>
                <p>Highest quality leather with natural grain intact. Requires minimal conditioning and develops a beautiful patina over time.</p>
            </div>

            <div class="border-l-4 border-blue-500 pl-4">
                <h3 class="text-lg font-semibold mb-2">Top-Grain Leather</h3>
                <p>Good quality leather with a smooth finish. Needs regular conditioning to maintain suppleness.</p>
            </div>

            <div class="border-l-4 border-green-500 pl-4">
                <h3 class="text-lg font-semibold mb-2">Genuine Leather</h3>
                <p>Requires more frequent care and conditioning to prevent drying and cracking.</p>
            </div>

            <div class="border-l-4 border-purple-500 pl-4">
                <h3 class="text-lg font-semibold mb-2">Suede</h3>
                <p>Needs special suede-specific products and brushes. More delicate than smooth leather.</p>
            </div>
        </div>
    </section>

    <section>
        <h2 class="text-2xl font-bold mb-4">Daily Care Routine</h2>
        <p class="mb-4">Proper daily care can significantly extend your leather product's life and maintain its appearance.</p>

        <div class="space-y-4">
            <div>
                <h3 class="text-xl font-semibold mb-3">Storage Tips</h3>
                <ul class="space-y-2">
                    <li>• Always hang leather jackets on wide, padded hangers to maintain their shape</li>
                    <li>• Store in a cool, dry place away from direct sunlight and heat sources</li>
                    <li>• Avoid plastic bags or covers that can trap moisture - use breathable garment bags</li>
                    <li>• Keep leather items away from damp areas to prevent mold and mildew</li>
                </ul>
            </div>

            <div>
                <h3 class="text-xl font-semibold mb-3">Cleaning Basics</h3>
                <ul class="space-y-2">
                    <li>• Use a soft, dry cloth to remove surface dirt and dust regularly</li>
                    <li>• For stubborn stains, use a slightly damp cloth with mild soap</li>
                    <li>• Always test cleaning products on a small, inconspicuous area first</li>
                    <li>• Never soak leather in water or use harsh chemicals</li>
                </ul>
            </div>
        </div>
    </section>

    <section>
        <h2 class="text-2xl font-bold mb-4">Conditioning and Protection</h2>
        <p class="mb-4">Regular conditioning keeps leather supple and prevents cracking, especially important in dry climates or during winter months.</p>

        <div class="space-y-4">
            <div>
                <h3 class="text-xl font-semibold mb-3">How Often to Condition</h3>
                <p class="mb-3">Condition your leather products every 3-6 months, or more frequently if you notice the leather becoming dry or stiff.</p>
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <p><strong>Tip:</strong> Increase conditioning frequency in dry climates or during winter when indoor heating can dry out leather.</p>
                </div>
            </div>

            <div>
                <h3 class="text-xl font-semibold mb-3">Choosing the Right Conditioner</h3>
                <ul class="space-y-2">
                    <li>• Use a high-quality leather conditioner specifically designed for your leather type</li>
                    <li>• Test on a small, inconspicuous area first to ensure compatibility</li>
                    <li>• Apply conditioner with a soft cloth in circular motions</li>
                    <li>• Allow the conditioner to absorb for 15-20 minutes, then buff with a clean cloth</li>
                </ul>
            </div>
        </div>
    </section>

    <section>
        <h2 class="text-2xl font-bold mb-4">Weather Protection</h2>
        
        <div class="space-y-4">
            <div>
                <h3 class="text-lg font-semibold mb-2">Rain and Moisture</h3>
                <p>If your leather gets wet, blot excess water with a soft cloth and let it air dry naturally. Never use direct heat sources like hair dryers or radiators, as they can cause cracking.</p>
            </div>

            <div>
                <h3 class="text-lg font-semibold mb-2">Sun Exposure</h3>
                <p>Prolonged sun exposure can fade and dry out leather. Store your items away from direct sunlight and consider using a leather protectant spray with UV protection.</p>
            </div>
        </div>
    </section>

    <section>
        <h2 class="text-2xl font-bold mb-4">Common Mistakes to Avoid</h2>
        <ul class="space-y-3">
            <li>⚠ <strong>Over-conditioning:</strong> Too much conditioner can make leather greasy and attract dirt</li>
            <li>⚠ <strong>Using harsh chemicals:</strong> Avoid household cleaners, alcohol, or acetone-based products</li>
            <li>⚠ <strong>Machine washing:</strong> Never put leather items in the washing machine or dryer</li>
            <li>⚠ <strong>Ignoring stains:</strong> Address spills and stains immediately for best results</li>
        </ul>
    </section>

    <section>
        <h2 class="text-2xl font-bold mb-4">Professional Care</h2>
        <p class="mb-4">For deep cleaning, restoration, or repair of valuable leather items, we recommend consulting a professional leather care specialist. They have the expertise and specialized products to handle:</p>

        <ul class="space-y-2 mb-4">
            <li>• Stubborn stains and discoloration</li>
            <li>• Tears, rips, or structural damage</li>
            <li>• Color restoration and refinishing</li>
            <li>• Mold or mildew removal</li>
        </ul>

        <div class="bg-gold-50 dark:bg-gold-900/20 border border-gold-200 dark:border-gold-800 rounded-lg p-4">
            <p><strong>Pro Tip:</strong> When in doubt, consult a professional leather care specialist. They can provide expert advice and services to restore your items to their original condition.</p>
        </div>
    </section>

    <section>
        <div class="mt-6">
            <img src="https://backend.nordflex.store/storage/images/blogs/2.jpeg" alt="Leather Care Guide" class="w-full h-auto rounded-lg" />
        </div>
    </section>
</div>
HTML;
    }
}
