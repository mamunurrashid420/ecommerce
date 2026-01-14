<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Clear existing data
            PaymentMethod::truncate();

            // Sample payment methods for Bangladesh
            $paymentMethods = [
                [
                    'name' => 'Bkash',
                    'name_bn' => 'বিকাশ',
                    'logo' => null, // You can add logo path or URL here
                    'information' => [
                        [
                            'label_name' => 'Merchant Number',
                            'label_value' => '+880 1XXXXXXXXX'
                        ],
                        [
                            'label_name' => 'Account Type',
                            'label_value' => 'Merchant'
                        ],
                        [
                            'label_name' => 'Payment Instructions',
                            'label_value' => 'Send money to the merchant number and provide transaction ID'
                        ]
                    ],
                    'description' => 'Pay using Bkash mobile banking service. Send money to our merchant account and provide the transaction ID.',
                    'description_bn' => 'বিকাশ মোবাইল ব্যাংকিং সেবা ব্যবহার করে পেমেন্ট করুন। আমাদের মার্চেন্ট একাউন্টে টাকা পাঠান এবং লেনদেন আইডি প্রদান করুন।',
                    'sort_order' => 1,
                    'is_active' => true,
                ],
                [
                    'name' => 'Nagad',
                    'name_bn' => 'নগদ',
                    'logo' => null,
                    'information' => [
                        [
                            'label_name' => 'Merchant Number',
                            'label_value' => '+880 1XXXXXXXXX'
                        ],
                        [
                            'label_name' => 'Account Type',
                            'label_value' => 'Merchant'
                        ],
                        [
                            'label_name' => 'Payment Instructions',
                            'label_value' => 'Send money to the merchant number and provide transaction ID'
                        ]
                    ],
                    'description' => 'Pay using Nagad mobile banking service. Send money to our merchant account and provide the transaction ID.',
                    'description_bn' => 'নগদ মোবাইল ব্যাংকিং সেবা ব্যবহার করে পেমেন্ট করুন। আমাদের মার্চেন্ট একাউন্টে টাকা পাঠান এবং লেনদেন আইডি প্রদান করুন।',
                    'sort_order' => 2,
                    'is_active' => true,
                ],
                [
                    'name' => 'Rocket',
                    'name_bn' => 'রকেট',
                    'logo' => null,
                    'information' => [
                        [
                            'label_name' => 'Merchant Number',
                            'label_value' => '+880 1XXXXXXXXX'
                        ],
                        [
                            'label_name' => 'Account Type',
                            'label_value' => 'Merchant'
                        ],
                        [
                            'label_name' => 'Payment Instructions',
                            'label_value' => 'Send money to the merchant number and provide transaction ID'
                        ]
                    ],
                    'description' => 'Pay using Rocket mobile banking service. Send money to our merchant account and provide the transaction ID.',
                    'description_bn' => 'রকেট মোবাইল ব্যাংকিং সেবা ব্যবহার করে পেমেন্ট করুন। আমাদের মার্চেন্ট একাউন্টে টাকা পাঠান এবং লেনদেন আইডি প্রদান করুন।',
                    'sort_order' => 3,
                    'is_active' => true,
                ],
                [
                    'name' => 'Bank Transfer',
                    'name_bn' => 'ব্যাংক ট্রান্সফার',
                    'logo' => null,
                    'information' => [
                        [
                            'label_name' => 'Bank Name',
                            'label_value' => 'Your Bank Name'
                        ],
                        [
                            'label_name' => 'Account Name',
                            'label_value' => 'Your Company Name'
                        ],
                        [
                            'label_name' => 'Account Number',
                            'label_value' => 'XXXXXXXXXXXXXXXXXX'
                        ],
                        [
                            'label_name' => 'Branch Name',
                            'label_value' => 'Your Branch'
                        ],
                        [
                            'label_name' => 'Routing Number',
                            'label_value' => 'XXXXXXXXX'
                        ]
                    ],
                    'description' => 'Transfer payment directly to our bank account. Please save the deposit slip and share with us.',
                    'description_bn' => 'সরাসরি আমাদের ব্যাংক অ্যাকাউন্টে পেমেন্ট ট্রান্সফার করুন। দয়া করে ডিপোজিট স্লিপ সংরক্ষণ করুন এবং আমাদের সাথে শেয়ার করুন।',
                    'sort_order' => 4,
                    'is_active' => true,
                ],
                [
                    'name' => 'Cash on Delivery',
                    'name_bn' => 'ক্যাশ অন ডেলিভারি',
                    'logo' => null,
                    'information' => [
                        [
                            'label_name' => 'Payment Method',
                            'label_value' => 'Cash'
                        ],
                        [
                            'label_name' => 'Payment Time',
                            'label_value' => 'Upon delivery'
                        ],
                        [
                            'label_name' => 'Instructions',
                            'label_value' => 'Pay the exact amount to the delivery person when you receive your order'
                        ]
                    ],
                    'description' => 'Pay with cash when you receive your order. Please keep exact amount ready.',
                    'description_bn' => 'অর্ডার গ্রহণের সময় নগদ অর্থ প্রদান করুন। দয়া করে সঠিক পরিমাণ টাকা প্রস্তুত রাখুন।',
                    'sort_order' => 5,
                    'is_active' => true,
                ],
                [
                    'name' => 'Upay',
                    'name_bn' => 'উপায়',
                    'logo' => null,
                    'information' => [
                        [
                            'label_name' => 'Merchant Number',
                            'label_value' => '+880 1XXXXXXXXX'
                        ],
                        [
                            'label_name' => 'Account Type',
                            'label_value' => 'Merchant'
                        ],
                        [
                            'label_name' => 'Payment Instructions',
                            'label_value' => 'Send money to the merchant number and provide transaction ID'
                        ]
                    ],
                    'description' => 'Pay using Upay mobile banking service. Send money to our merchant account and provide the transaction ID.',
                    'description_bn' => 'উপায় মোবাইল ব্যাংকিং সেবা ব্যবহার করে পেমেন্ট করুন। আমাদের মার্চেন্ট একাউন্টে টাকা পাঠান এবং লেনদেন আইডি প্রদান করুন।',
                    'sort_order' => 6,
                    'is_active' => true,
                ],
            ];

            foreach ($paymentMethods as $method) {
                PaymentMethod::create($method);
            }

            $this->command->info('Payment methods seeded successfully!');
        });
    }
}

