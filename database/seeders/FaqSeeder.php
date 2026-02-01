<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Faq;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            [
                'question' => 'How do I place an order?',
                'answer' => 'To place an order, simply browse our products, add your desired items to the cart, and proceed to checkout. Follow the on-screen instructions to complete your purchase.',
                'order' => 1,
            ],
            [
                'question' => 'What payment methods do you accept?',
                'answer' => 'We accept all major credit cards, PayPal, and Apple Pay. All payments are processed securely.',
                'order' => 2,
            ],
            [
                'question' => 'Can I track my order?',
                'answer' => 'Yes, once your order is shipped, you will receive a tracking number via email. You can use this number to track your shipment on our website.',
                'order' => 3,
            ],
            [
                'question' => 'Is my personal information safe?',
                'answer' => 'Absolutely. We use industry-standard encryption to protect your data and never share your information with third parties.',
                'order' => 4,
            ],
            [
                'question' => 'Do you ship internationally?',
                'answer' => 'Yes, we ship to most countries worldwide. Shipping costs and delivery times vary depending on your location.',
                'order' => 5,
            ],
            [
                'question' => 'What is your return policy?',
                'answer' => 'We offer a 30-day return policy on most items. Please visit our Returns & Exchanges page for more details.',
                'order' => 6,
            ],
            [
                'question' => 'How do I care for my product?',
                'answer' => 'Care instructions are provided with each product. If you have specific questions, feel free to contact our support team.',
                'order' => 7,
            ],
            [
                'question' => 'Are your products authentic?',
                'answer' => 'Yes, all our products are 100% authentic and sourced directly from reputable manufacturers.',
                'order' => 8,
            ],
            [
                'question' => 'Can I customize my order?',
                'answer' => 'Some products offer customization options. Please check the product page or contact us for more information.',
                'order' => 9,
            ],
            [
                'question' => 'How can I contact customer support?',
                'answer' => 'You can reach our customer support team via email at support@nordflex.store or by phone at +358449782549.',
                'order' => 10,
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::create($faq);
        }
    }
}
