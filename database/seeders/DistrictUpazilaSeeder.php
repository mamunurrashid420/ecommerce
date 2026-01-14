<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\District;
use App\Models\Upazila;
use Illuminate\Support\Facades\DB;

class DistrictUpazilaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Clear existing data
            Upazila::truncate();
            District::truncate();

            // Bangladesh Districts and Upazillas Data
            $data = [
                // Dhaka Division
                [
                    'district' => 'Dhaka',
                    'district_bn' => 'ঢাকা',
                    'division' => 'Dhaka',
                    'division_bn' => 'ঢাকা',
                    'upazillas' => [
                        ['name' => 'Dhamrai', 'name_bn' => 'ধামরাই'],
                        ['name' => 'Dohar', 'name_bn' => 'দোহার'],
                        ['name' => 'Keraniganj', 'name_bn' => 'কেরানীগঞ্জ'],
                        ['name' => 'Nawabganj', 'name_bn' => 'নবাবগঞ্জ'],
                        ['name' => 'Savar', 'name_bn' => 'সাভার'],
                        ['name' => 'Tejgaon', 'name_bn' => 'তেজগাঁও'],
                        ['name' => 'Mohammadpur', 'name_bn' => 'মোহাম্মদপুর'],
                        ['name' => 'Gulshan', 'name_bn' => 'গুলশান'],
                        ['name' => 'Mirpur', 'name_bn' => 'মিরপুর'],
                        ['name' => 'Pallabi', 'name_bn' => 'পল্লবী'],
                        ['name' => 'Uttara', 'name_bn' => 'উত্তরা'],
                    ]
                ],
                [
                    'district' => 'Faridpur',
                    'district_bn' => 'ফরিদপুর',
                    'division' => 'Dhaka',
                    'division_bn' => 'ঢাকা',
                    'upazillas' => [
                        ['name' => 'Alfadanga', 'name_bn' => 'আলফাডাঙ্গা'],
                        ['name' => 'Bhanga', 'name_bn' => 'ভাঙ্গা'],
                        ['name' => 'Boalmari', 'name_bn' => 'বোয়ালমারী'],
                        ['name' => 'Charbhadrasan', 'name_bn' => 'চরভদ্রাসন'],
                        ['name' => 'Faridpur Sadar', 'name_bn' => 'ফরিদপুর সদর'],
                        ['name' => 'Madhukhali', 'name_bn' => 'মধুখালী'],
                        ['name' => 'Nagarkanda', 'name_bn' => 'নগরকান্দা'],
                        ['name' => 'Sadarpur', 'name_bn' => 'সদরপুর'],
                        ['name' => 'Saltha', 'name_bn' => 'সালথা'],
                    ]
                ],
                [
                    'district' => 'Gazipur',
                    'district_bn' => 'গাজীপুর',
                    'division' => 'Dhaka',
                    'division_bn' => 'ঢাকা',
                    'upazillas' => [
                        ['name' => 'Gazipur Sadar', 'name_bn' => 'গাজীপুর সদর'],
                        ['name' => 'Kaliakair', 'name_bn' => 'কালিয়াকৈর'],
                        ['name' => 'Kaliganj', 'name_bn' => 'কালীগঞ্জ'],
                        ['name' => 'Kapasia', 'name_bn' => 'কাপাসিয়া'],
                        ['name' => 'Sreepur', 'name_bn' => 'শ্রীপুর'],
                    ]
                ],
                [
                    'district' => 'Gopalganj',
                    'district_bn' => 'গোপালগঞ্জ',
                    'division' => 'Dhaka',
                    'division_bn' => 'ঢাকা',
                    'upazillas' => [
                        ['name' => 'Gopalganj Sadar', 'name_bn' => 'গোপালগঞ্জ সদর'],
                        ['name' => 'Kashiani', 'name_bn' => 'কাশিয়ানী'],
                        ['name' => 'Kotalipara', 'name_bn' => 'কোটালীপাড়া'],
                        ['name' => 'Muksudpur', 'name_bn' => 'মুকসুদপুর'],
                        ['name' => 'Tungipara', 'name_bn' => 'টুঙ্গীপাড়া'],
                    ]
                ],
                [
                    'district' => 'Kishoreganj',
                    'district_bn' => 'কিশোরগঞ্জ',
                    'division' => 'Dhaka',
                    'division_bn' => 'ঢাকা',
                    'upazillas' => [
                        ['name' => 'Austagram', 'name_bn' => 'অষ্টগ্রাম'],
                        ['name' => 'Bajitpur', 'name_bn' => 'বাজিতপুর'],
                        ['name' => 'Bhairab', 'name_bn' => 'ভৈরব'],
                        ['name' => 'Hossainpur', 'name_bn' => 'হোসেনপুর'],
                        ['name' => 'Itna', 'name_bn' => 'ইটনা'],
                        ['name' => 'Karimganj', 'name_bn' => 'করিমগঞ্জ'],
                        ['name' => 'Katiadi', 'name_bn' => 'কাতিয়াদি'],
                        ['name' => 'Kishoreganj Sadar', 'name_bn' => 'কিশোরগঞ্জ সদর'],
                        ['name' => 'Kuliarchar', 'name_bn' => 'কুলিয়ারচর'],
                        ['name' => 'Mithamain', 'name_bn' => 'মিঠামইন'],
                        ['name' => 'Nikli', 'name_bn' => 'নিকলী'],
                        ['name' => 'Pakundia', 'name_bn' => 'পাকুন্দিয়া'],
                        ['name' => 'Tarail', 'name_bn' => 'তাড়াইল'],
                    ]
                ],
                [
                    'district' => 'Madaripur',
                    'district_bn' => 'মাদারীপুর',
                    'division' => 'Dhaka',
                    'division_bn' => 'ঢাকা',
                    'upazillas' => [
                        ['name' => 'Madaripur Sadar', 'name_bn' => 'মাদারীপুর সদর'],
                        ['name' => 'Kalkini', 'name_bn' => 'কালকিনি'],
                        ['name' => 'Rajoir', 'name_bn' => 'রাজইর'],
                        ['name' => 'Shibchar', 'name_bn' => 'শিবচর'],
                    ]
                ],
                [
                    'district' => 'Manikganj',
                    'district_bn' => 'মানিকগঞ্জ',
                    'division' => 'Dhaka',
                    'division_bn' => 'ঢাকা',
                    'upazillas' => [
                        ['name' => 'Manikganj Sadar', 'name_bn' => 'মানিকগঞ্জ সদর'],
                        ['name' => 'Daulatpur', 'name_bn' => 'দৌলতপুর'],
                        ['name' => 'Ghior', 'name_bn' => 'ঘিওর'],
                        ['name' => 'Harirampur', 'name_bn' => 'হরিরামপুর'],
                        ['name' => 'Saturia', 'name_bn' => 'সাটুরিয়া'],
                        ['name' => 'Shibalaya', 'name_bn' => 'শিবালয়'],
                        ['name' => 'Singair', 'name_bn' => 'সিঙ্গাইর'],
                    ]
                ],
                [
                    'district' => 'Munshiganj',
                    'district_bn' => 'মুন্সিগঞ্জ',
                    'division' => 'Dhaka',
                    'division_bn' => 'ঢাকা',
                    'upazillas' => [
                        ['name' => 'Munshiganj Sadar', 'name_bn' => 'মুন্সিগঞ্জ সদর'],
                        ['name' => 'Gazaria', 'name_bn' => 'গজারিয়া'],
                        ['name' => 'Lohajang', 'name_bn' => 'লোহাজং'],
                        ['name' => 'Serajdikhan', 'name_bn' => 'সিরাজদিখান'],
                        ['name' => 'Sreenagar', 'name_bn' => 'শ্রীনগর'],
                        ['name' => 'Tongibari', 'name_bn' => 'টংগীবাড়ি'],
                    ]
                ],
                [
                    'district' => 'Narayanganj',
                    'district_bn' => 'নারায়ণগঞ্জ',
                    'division' => 'Dhaka',
                    'division_bn' => 'ঢাকা',
                    'upazillas' => [
                        ['name' => 'Narayanganj Sadar', 'name_bn' => 'নারায়ণগঞ্জ সদর'],
                        ['name' => 'Araihazar', 'name_bn' => 'আড়াইহাজার'],
                        ['name' => 'Bandar', 'name_bn' => 'বন্দর'],
                        ['name' => 'Rupganj', 'name_bn' => 'রূপগঞ্জ'],
                        ['name' => 'Sonargaon', 'name_bn' => 'সোনারগাঁও'],
                    ]
                ],
                [
                    'district' => 'Narsingdi',
                    'district_bn' => 'নরসিংদী',
                    'division' => 'Dhaka',
                    'division_bn' => 'ঢাকা',
                    'upazillas' => [
                        ['name' => 'Narsingdi Sadar', 'name_bn' => 'নরসিংদী সদর'],
                        ['name' => 'Belabo', 'name_bn' => 'বেলাবো'],
                        ['name' => 'Monohardi', 'name_bn' => 'মনোহরদী'],
                        ['name' => 'Palash', 'name_bn' => 'পলাশ'],
                        ['name' => 'Raipura', 'name_bn' => 'রায়পুরা'],
                        ['name' => 'Shibpur', 'name_bn' => 'শিবপুর'],
                    ]
                ],
                [
                    'district' => 'Rajbari',
                    'district_bn' => 'রাজবাড়ী',
                    'division' => 'Dhaka',
                    'division_bn' => 'ঢাকা',
                    'upazillas' => [
                        ['name' => 'Rajbari Sadar', 'name_bn' => 'রাজবাড়ী সদর'],
                        ['name' => 'Baliakandi', 'name_bn' => 'বালিয়াকান্দি'],
                        ['name' => 'Goalandaghat', 'name_bn' => 'গোয়ালন্দ ঘাট'],
                        ['name' => 'Pangsha', 'name_bn' => 'পাংশা'],
                        ['name' => 'Kalukhali', 'name_bn' => 'কালুখালি'],
                    ]
                ],
                [
                    'district' => 'Shariatpur',
                    'district_bn' => 'শরীয়তপুর',
                    'division' => 'Dhaka',
                    'division_bn' => 'ঢাকা',
                    'upazillas' => [
                        ['name' => 'Shariatpur Sadar', 'name_bn' => 'শরীয়তপুর সদর'],
                        ['name' => 'Bhedarganj', 'name_bn' => 'ভেদরগঞ্জ'],
                        ['name' => 'Damudya', 'name_bn' => 'ডামুড্যা'],
                        ['name' => 'Gosairhat', 'name_bn' => 'গোসাইরহাট'],
                        ['name' => 'Naria', 'name_bn' => 'নড়িয়া'],
                        ['name' => 'Zajira', 'name_bn' => 'জাজিরা'],
                    ]
                ],
                [
                    'district' => 'Tangail',
                    'district_bn' => 'টাঙ্গাইল',
                    'division' => 'Dhaka',
                    'division_bn' => 'ঢাকা',
                    'upazillas' => [
                        ['name' => 'Tangail Sadar', 'name_bn' => 'টাঙ্গাইল সদর'],
                        ['name' => 'Basail', 'name_bn' => 'বাসাইল'],
                        ['name' => 'Bhuapur', 'name_bn' => 'ভুয়াপুর'],
                        ['name' => 'Delduar', 'name_bn' => 'দেলদুয়ার'],
                        ['name' => 'Dhanbari', 'name_bn' => 'ধনবাড়ি'],
                        ['name' => 'Ghatail', 'name_bn' => 'ঘাটাইল'],
                        ['name' => 'Gopalpur', 'name_bn' => 'গোপালপুর'],
                        ['name' => 'Kalihati', 'name_bn' => 'কালিহাতী'],
                        ['name' => 'Madhupur', 'name_bn' => 'মধুপুর'],
                        ['name' => 'Mirzapur', 'name_bn' => 'মির্জাপুর'],
                        ['name' => 'Nagarpur', 'name_bn' => 'নাগরপুর'],
                        ['name' => 'Sakhipur', 'name_bn' => 'সখিপুর'],
                    ]
                ],

                // Chittagong Division
                [
                    'district' => 'Chittagong',
                    'district_bn' => 'চট্টগ্রাম',
                    'division' => 'Chittagong',
                    'division_bn' => 'চট্টগ্রাম',
                    'upazillas' => [
                        ['name' => 'Anwara', 'name_bn' => 'আনোয়ারা'],
                        ['name' => 'Banshkhali', 'name_bn' => 'বাঁশখালী'],
                        ['name' => 'Boalkhali', 'name_bn' => 'বোয়ালখালী'],
                        ['name' => 'Chandanaish', 'name_bn' => 'চন্দনাইশ'],
                        ['name' => 'Fatikchhari', 'name_bn' => 'ফটিকছড়ি'],
                        ['name' => 'Hathazari', 'name_bn' => 'হাটহাজারী'],
                        ['name' => 'Lohagara', 'name_bn' => 'লোহাগাড়া'],
                        ['name' => 'Mirsharai', 'name_bn' => 'মিরসরাই'],
                        ['name' => 'Patiya', 'name_bn' => 'পটিয়া'],
                        ['name' => 'Rangunia', 'name_bn' => 'রাঙ্গুনিয়া'],
                        ['name' => 'Raozan', 'name_bn' => 'রাউজান'],
                        ['name' => 'Sandwip', 'name_bn' => 'সন্দ্বীপ'],
                        ['name' => 'Satkania', 'name_bn' => 'সাতকানিয়া'],
                        ['name' => 'Sitakunda', 'name_bn' => 'সীতাকুণ্ড'],
                    ]
                ],
                [
                    'district' => 'Bandarban',
                    'district_bn' => 'বান্দরবান',
                    'division' => 'Chittagong',
                    'division_bn' => 'চট্টগ্রাম',
                    'upazillas' => [
                        ['name' => 'Bandarban Sadar', 'name_bn' => 'বান্দরবান সদর'],
                        ['name' => 'Alikadam', 'name_bn' => 'আলীকদম'],
                        ['name' => 'Lama', 'name_bn' => 'লামা'],
                        ['name' => 'Naikhongchhari', 'name_bn' => 'নাইক্ষ্যংছড়ি'],
                        ['name' => 'Rowangchhari', 'name_bn' => 'রোয়াংছড়ি'],
                        ['name' => 'Ruma', 'name_bn' => 'রুমা'],
                        ['name' => 'Thanchi', 'name_bn' => 'থানচি'],
                    ]
                ],
                [
                    'district' => 'Brahmanbaria',
                    'district_bn' => 'ব্রাহ্মণবাড়িয়া',
                    'division' => 'Chittagong',
                    'division_bn' => 'চট্টগ্রাম',
                    'upazillas' => [
                        ['name' => 'Brahmanbaria Sadar', 'name_bn' => 'ব্রাহ্মণবাড়িয়া সদর'],
                        ['name' => 'Akhaura', 'name_bn' => 'আখাউড়া'],
                        ['name' => 'Ashuganj', 'name_bn' => 'আশুগঞ্জ'],
                        ['name' => 'Bancharampur', 'name_bn' => 'বাঞ্ছারামপুর'],
                        ['name' => 'Bijoynagar', 'name_bn' => 'বিজয়নগর'],
                        ['name' => 'Kasba', 'name_bn' => 'কসবা'],
                        ['name' => 'Nabinagar', 'name_bn' => 'নবীনগর'],
                        ['name' => 'Nasirnagar', 'name_bn' => 'নাসিরনগর'],
                        ['name' => 'Sarail', 'name_bn' => 'সরাইল'],
                    ]
                ],
                [
                    'district' => 'Chandpur',
                    'district_bn' => 'চাঁদপুর',
                    'division' => 'Chittagong',
                    'division_bn' => 'চট্টগ্রাম',
                    'upazillas' => [
                        ['name' => 'Chandpur Sadar', 'name_bn' => 'চাঁদপুর সদর'],
                        ['name' => 'Faridganj', 'name_bn' => 'ফরিদগঞ্জ'],
                        ['name' => 'Haimchar', 'name_bn' => 'হাইমচর'],
                        ['name' => 'Hajiganj', 'name_bn' => 'হাজীগঞ্জ'],
                        ['name' => 'Kachua', 'name_bn' => 'কচুয়া'],
                        ['name' => 'Matlab Dakshin', 'name_bn' => 'মতলব দক্ষিণ'],
                        ['name' => 'Matlab Uttar', 'name_bn' => 'মতলব উত্তর'],
                        ['name' => 'Shahrasti', 'name_bn' => 'শাহরাস্তি'],
                    ]
                ],
                [
                    'district' => 'Cumilla',
                    'district_bn' => 'কুমিল্লা',
                    'division' => 'Chittagong',
                    'division_bn' => 'চট্টগ্রাম',
                    'upazillas' => [
                        ['name' => 'Cumilla Sadar', 'name_bn' => 'কুমিল্লা সদর'],
                        ['name' => 'Barura', 'name_bn' => 'বরুড়া'],
                        ['name' => 'Brahmanpara', 'name_bn' => 'ব্রাহ্মণপাড়া'],
                        ['name' => 'Burichang', 'name_bn' => 'বুড়িচং'],
                        ['name' => 'Chandina', 'name_bn' => 'চান্দিনা'],
                        ['name' => 'Chauddagram', 'name_bn' => 'চৌদ্দগ্রাম'],
                        ['name' => 'Daudkandi', 'name_bn' => 'দাউদকান্দি'],
                        ['name' => 'Debidwar', 'name_bn' => 'দেবিদ্বার'],
                        ['name' => 'Homna', 'name_bn' => 'হোমনা'],
                        ['name' => 'Laksam', 'name_bn' => 'লাকসাম'],
                        ['name' => 'Meghna', 'name_bn' => 'মেঘনা'],
                        ['name' => 'Monohargonj', 'name_bn' => 'মনোহরগঞ্জ'],
                        ['name' => 'Muradnagar', 'name_bn' => 'মুরাদনগর'],
                        ['name' => 'Nangalkot', 'name_bn' => 'নাঙ্গলকোট'],
                        ['name' => 'Titas', 'name_bn' => 'তিতাস'],
                    ]
                ],
                [
                    'district' => "Cox's Bazar",
                    'district_bn' => "কক্সবাজার",
                    'division' => 'Chittagong',
                    'division_bn' => 'চট্টগ্রাম',
                    'upazillas' => [
                        ['name' => "Cox's Bazar Sadar", 'name_bn' => 'কক্সবাজার সদর'],
                        ['name' => 'Chakaria', 'name_bn' => 'চকরিয়া'],
                        ['name' => 'Kutubdia', 'name_bn' => 'কুতুবদিয়া'],
                        ['name' => 'Maheshkhali', 'name_bn' => 'মহেশখালী'],
                        ['name' => 'Pekua', 'name_bn' => 'পেকুয়া'],
                        ['name' => 'Ramu', 'name_bn' => 'রামু'],
                        ['name' => 'Teknaf', 'name_bn' => 'টেকনাফ'],
                        ['name' => 'Ukhia', 'name_bn' => 'উখিয়া'],
                    ]
                ],
                [
                    'district' => 'Feni',
                    'district_bn' => 'ফেনী',
                    'division' => 'Chittagong',
                    'division_bn' => 'চট্টগ্রাম',
                    'upazillas' => [
                        ['name' => 'Feni Sadar', 'name_bn' => 'ফেনী সদর'],
                        ['name' => 'Chhagalnaiya', 'name_bn' => 'ছাগলনাইয়া'],
                        ['name' => 'Daganbhuiyan', 'name_bn' => 'দাগনভুইয়া'],
                        ['name' => 'Fulgazi', 'name_bn' => 'ফুলগাজী'],
                        ['name' => 'Parshuram', 'name_bn' => 'পরশুরাম'],
                        ['name' => 'Sonagazi', 'name_bn' => 'সোনাগাজী'],
                    ]
                ],
                [
                    'district' => 'Khagrachari',
                    'district_bn' => 'খাগড়াছড়ি',
                    'division' => 'Chittagong',
                    'division_bn' => 'চট্টগ্রাম',
                    'upazillas' => [
                        ['name' => 'Khagrachari Sadar', 'name_bn' => 'খাগড়াছড়ি সদর'],
                        ['name' => 'Dighinala', 'name_bn' => 'দিঘিনালা'],
                        ['name' => 'Lakshmichhari', 'name_bn' => 'লক্ষ্মীছড়ি'],
                        ['name' => 'Mahalchhari', 'name_bn' => 'মহালছড়ি'],
                        ['name' => 'Manikchhari', 'name_bn' => 'মানিকছড়ি'],
                        ['name' => 'Matiranga', 'name_bn' => 'মাটিরাঙ্গা'],
                        ['name' => 'Panchhari', 'name_bn' => 'পানছড়ি'],
                        ['name' => 'Ramgarh', 'name_bn' => 'রামগড়'],
                    ]
                ],
                [
                    'district' => 'Lakshmipur',
                    'district_bn' => 'লক্ষ্মীপুর',
                    'division' => 'Chittagong',
                    'division_bn' => 'চট্টগ্রাম',
                    'upazillas' => [
                        ['name' => 'Lakshmipur Sadar', 'name_bn' => 'লক্ষ্মীপুর সদর'],
                        ['name' => 'Raipur', 'name_bn' => 'রায়পুর'],
                        ['name' => 'Ramganj', 'name_bn' => 'রামগঞ্জ'],
                        ['name' => 'Ramgati', 'name_bn' => 'রামগতি'],
                        ['name' => 'Kamalnagar', 'name_bn' => 'কমলনগর'],
                    ]
                ],
                [
                    'district' => 'Noakhali',
                    'district_bn' => 'নোয়াখালী',
                    'division' => 'Chittagong',
                    'division_bn' => 'চট্টগ্রাম',
                    'upazillas' => [
                        ['name' => 'Noakhali Sadar', 'name_bn' => 'নোয়াখালী সদর'],
                        ['name' => 'Begumganj', 'name_bn' => 'বেগমগঞ্জ'],
                        ['name' => 'Chatkhil', 'name_bn' => 'চাটখিল'],
                        ['name' => 'Companiganj', 'name_bn' => 'কোম্পানীগঞ্জ'],
                        ['name' => 'Hatiya', 'name_bn' => 'হাতিয়া'],
                        ['name' => 'Kabirhat', 'name_bn' => 'কবিরহাট'],
                        ['name' => 'Senbagh', 'name_bn' => 'সেনবাগ'],
                        ['name' => 'Sonaimuri', 'name_bn' => 'সোনাইমুড়ি'],
                        ['name' => 'Subarnachar', 'name_bn' => 'সুবর্ণচর'],
                    ]
                ],
                [
                    'district' => 'Rangamati',
                    'district_bn' => 'রাঙ্গামাটি',
                    'division' => 'Chittagong',
                    'division_bn' => 'চট্টগ্রাম',
                    'upazillas' => [
                        ['name' => 'Rangamati Sadar', 'name_bn' => 'রাঙ্গামাটি সদর'],
                        ['name' => 'Baghaichhari', 'name_bn' => 'বাঘাইছড়ি'],
                        ['name' => 'Barkal', 'name_bn' => 'বরকল'],
                        ['name' => 'Belaichhari', 'name_bn' => 'বিলাইছড়ি'],
                        ['name' => 'Juraichhari', 'name_bn' => 'জুরাছড়ি'],
                        ['name' => 'Kaptai', 'name_bn' => 'কাপ্তাই'],
                        ['name' => 'Kaukhali', 'name_bn' => 'কাউখালি'],
                        ['name' => 'Langadu', 'name_bn' => 'লংগদু'],
                        ['name' => 'Naniarchar', 'name_bn' => 'নানিয়ারচর'],
                        ['name' => 'Rajasthali', 'name_bn' => 'রাজস্থলী'],
                    ]
                ],

                // Rajshahi Division
                [
                    'district' => 'Bogura',
                    'district_bn' => 'বগুড়া',
                    'division' => 'Rajshahi',
                    'division_bn' => 'রাজশাহী',
                    'upazillas' => [
                        ['name' => 'Bogura Sadar', 'name_bn' => 'বগুড়া সদর'],
                        ['name' => 'Adamdighi', 'name_bn' => 'আদমদিঘি'],
                        ['name' => 'Dhunat', 'name_bn' => 'ধুনট'],
                        ['name' => 'Dhupchanchia', 'name_bn' => 'দুপচাঁচিয়া'],
                        ['name' => 'Gabtali', 'name_bn' => 'গাবতলি'],
                        ['name' => 'Kahaloo', 'name_bn' => 'কাহালু'],
                        ['name' => 'Nandigram', 'name_bn' => 'নন্দিগ্রাম'],
                        ['name' => 'Sariakandi', 'name_bn' => 'সারিয়াকান্দি'],
                        ['name' => 'Shajahanpur', 'name_bn' => 'শাজাহানপুর'],
                        ['name' => 'Sherpur', 'name_bn' => 'শেরপুর'],
                        ['name' => 'Shibganj', 'name_bn' => 'শিবগঞ্জ'],
                        ['name' => 'Sonatola', 'name_bn' => 'সোনাতলা'],
                    ]
                ],
                [
                    'district' => 'Joypurhat',
                    'district_bn' => 'জয়পুরহাট',
                    'division' => 'Rajshahi',
                    'division_bn' => 'রাজশাহী',
                    'upazillas' => [
                        ['name' => 'Joypurhat Sadar', 'name_bn' => 'জয়পুরহাট সদর'],
                        ['name' => 'Akkelpur', 'name_bn' => 'আক্কেলপুর'],
                        ['name' => 'Kalai', 'name_bn' => 'কালাই'],
                        ['name' => 'Khetlal', 'name_bn' => 'ক্ষেতলাল'],
                        ['name' => 'Panchbibi', 'name_bn' => 'পাঁচবিবি'],
                    ]
                ],
                [
                    'district' => 'Naogaon',
                    'district_bn' => 'নওগাঁ',
                    'division' => 'Rajshahi',
                    'division_bn' => 'রাজশাহী',
                    'upazillas' => [
                        ['name' => 'Naogaon Sadar', 'name_bn' => 'নওগাঁ সদর'],
                        ['name' => 'Atrai', 'name_bn' => 'আত্রাই'],
                        ['name' => 'Badalgachhi', 'name_bn' => 'বদলগাছী'],
                        ['name' => 'Dhamoirhat', 'name_bn' => 'ধামইরহাট'],
                        ['name' => 'Manda', 'name_bn' => 'মান্দা'],
                        ['name' => 'Mahadebpur', 'name_bn' => 'মহাদেবপুর'],
                        ['name' => 'Niamatpur', 'name_bn' => 'নিয়ামতপুর'],
                        ['name' => 'Patnitala', 'name_bn' => 'পত্নিতলা'],
                        ['name' => 'Porsha', 'name_bn' => 'পোরশা'],
                        ['name' => 'Raninagar', 'name_bn' => 'রাণীনগর'],
                        ['name' => 'Sapahar', 'name_bn' => 'সাপাহার'],
                    ]
                ],
                [
                    'district' => 'Natore',
                    'district_bn' => 'নাটোর',
                    'division' => 'Rajshahi',
                    'division_bn' => 'রাজশাহী',
                    'upazillas' => [
                        ['name' => 'Natore Sadar', 'name_bn' => 'নাটোর সদর'],
                        ['name' => 'Bagatipara', 'name_bn' => 'বাগাতিপাড়া'],
                        ['name' => 'Baraigram', 'name_bn' => 'বড়াইগ্রাম'],
                        ['name' => 'Gurudaspur', 'name_bn' => 'গুরুদাসপুর'],
                        ['name' => 'Lalpur', 'name_bn' => 'লালপুর'],
                        ['name' => 'Naldanga', 'name_bn' => 'নলডাঙ্গা'],
                        ['name' => 'Singra', 'name_bn' => 'সিংড়া'],
                    ]
                ],
                [
                    'district' => 'Chapainawabganj',
                    'district_bn' => 'চাঁপাইনবাবগঞ্জ',
                    'division' => 'Rajshahi',
                    'division_bn' => 'রাজশাহী',
                    'upazillas' => [
                        ['name' => 'Chapainawabganj Sadar', 'name_bn' => 'চাঁপাইনবাবগঞ্জ সদর'],
                        ['name' => 'Bholahat', 'name_bn' => 'ভোলাহাট'],
                        ['name' => 'Gomastapur', 'name_bn' => 'গোমস্তাপুর'],
                        ['name' => 'Nachole', 'name_bn' => 'নাচোল'],
                        ['name' => 'Shibganj', 'name_bn' => 'শিবগঞ্জ'],
                    ]
                ],
                [
                    'district' => 'Pabna',
                    'district_bn' => 'পাবনা',
                    'division' => 'Rajshahi',
                    'division_bn' => 'রাজশাহী',
                    'upazillas' => [
                        ['name' => 'Pabna Sadar', 'name_bn' => 'পাবনা সদর'],
                        ['name' => 'Atgharia', 'name_bn' => 'আটঘরিয়া'],
                        ['name' => 'Bera', 'name_bn' => 'বেড়া'],
                        ['name' => 'Bhangura', 'name_bn' => 'ভাঙ্গুড়া'],
                        ['name' => 'Chatmohar', 'name_bn' => 'চাটমোহর'],
                        ['name' => 'Faridpur', 'name_bn' => 'ফরিদপুর'],
                        ['name' => 'Ishwardi', 'name_bn' => 'ঈশ্বরদী'],
                        ['name' => 'Santhia', 'name_bn' => 'সাঁথিয়া'],
                        ['name' => 'Sujanagar', 'name_bn' => 'সুজানগর'],
                    ]
                ],
                [
                    'district' => 'Rajshahi',
                    'district_bn' => 'রাজশাহী',
                    'division' => 'Rajshahi',
                    'division_bn' => 'রাজশাহী',
                    'upazillas' => [
                        ['name' => 'Rajshahi Sadar', 'name_bn' => 'রাজশাহী সদর'],
                        ['name' => 'Bagha', 'name_bn' => 'বাঘা'],
                        ['name' => 'Bagmara', 'name_bn' => 'বাগমারা'],
                        ['name' => 'Charghat', 'name_bn' => 'চারঘাট'],
                        ['name' => 'Durgapur', 'name_bn' => 'দুর্গাপুর'],
                        ['name' => 'Godagari', 'name_bn' => 'গোদাগাড়ি'],
                        ['name' => 'Mohanpur', 'name_bn' => 'মোহনপুর'],
                        ['name' => 'Paba', 'name_bn' => 'পবা'],
                        ['name' => 'Puthia', 'name_bn' => 'পুঠিয়া'],
                        ['name' => 'Tanore', 'name_bn' => 'তানোর'],
                    ]
                ],
                [
                    'district' => 'Sirajganj',
                    'district_bn' => 'সিরাজগঞ্জ',
                    'division' => 'Rajshahi',
                    'division_bn' => 'রাজশাহী',
                    'upazillas' => [
                        ['name' => 'Sirajganj Sadar', 'name_bn' => 'সিরাজগঞ্জ সদর'],
                        ['name' => 'Belkuchi', 'name_bn' => 'বেলকুচি'],
                        ['name' => 'Chauhali', 'name_bn' => 'চৌহালি'],
                        ['name' => 'Kamarkhanda', 'name_bn' => 'কামারখন্দ'],
                        ['name' => 'Kazipur', 'name_bn' => 'কাজীপুর'],
                        ['name' => 'Raiganj', 'name_bn' => 'রায়গঞ্জ'],
                        ['name' => 'Shahjadpur', 'name_bn' => 'শাহজাদপুর'],
                        ['name' => 'Tarash', 'name_bn' => 'তাড়াশ'],
                        ['name' => 'Ullahpara', 'name_bn' => 'উল্লাপাড়া'],
                    ]
                ],

                // Khulna Division
                [
                    'district' => 'Bagerhat',
                    'district_bn' => 'বাগেরহাট',
                    'division' => 'Khulna',
                    'division_bn' => 'খুলনা',
                    'upazillas' => [
                        ['name' => 'Bagerhat Sadar', 'name_bn' => 'বাগেরহাট সদর'],
                        ['name' => 'Chitalmari', 'name_bn' => 'চিতলমারি'],
                        ['name' => 'Fakirhat', 'name_bn' => 'ফকিরহাট'],
                        ['name' => 'Kachua', 'name_bn' => 'কচুয়া'],
                        ['name' => 'Mollahat', 'name_bn' => 'মোল্লাহাট'],
                        ['name' => 'Mongla', 'name_bn' => 'মংলা'],
                        ['name' => 'Morrelganj', 'name_bn' => 'মোড়েলগঞ্জ'],
                        ['name' => 'Rampal', 'name_bn' => 'রামপাল'],
                        ['name' => 'Sarankhola', 'name_bn' => 'শরণখোলা'],
                    ]
                ],
                [
                    'district' => 'Chuadanga',
                    'district_bn' => 'চুয়াডাঙ্গা',
                    'division' => 'Khulna',
                    'division_bn' => 'খুলনা',
                    'upazillas' => [
                        ['name' => 'Chuadanga Sadar', 'name_bn' => 'চুয়াডাঙ্গা সদর'],
                        ['name' => 'Alamdanga', 'name_bn' => 'আলমডাঙ্গা'],
                        ['name' => 'Damurhuda', 'name_bn' => 'দামুড়হুদা'],
                        ['name' => 'Jibannagar', 'name_bn' => 'জীবননগর'],
                    ]
                ],
                [
                    'district' => 'Jashore',
                    'district_bn' => 'যশোর',
                    'division' => 'Khulna',
                    'division_bn' => 'খুলনা',
                    'upazillas' => [
                        ['name' => 'Jashore Sadar', 'name_bn' => 'যশোর সদর'],
                        ['name' => 'Abhaynagar', 'name_bn' => 'অভয়নগর'],
                        ['name' => 'Bagherpara', 'name_bn' => 'বাঘারপাড়া'],
                        ['name' => 'Chaugachha', 'name_bn' => 'চৌগাছা'],
                        ['name' => 'Jhikargachha', 'name_bn' => 'ঝিকরগাছা'],
                        ['name' => 'Keshabpur', 'name_bn' => 'কেশবপুর'],
                        ['name' => 'Manirampur', 'name_bn' => 'মণিরামপুর'],
                        ['name' => 'Sharsha', 'name_bn' => 'শার্শা'],
                    ]
                ],
                [
                    'district' => 'Jhenaidah',
                    'district_bn' => 'ঝিনাইদহ',
                    'division' => 'Khulna',
                    'division_bn' => 'খুলনা',
                    'upazillas' => [
                        ['name' => 'Jhenaidah Sadar', 'name_bn' => 'ঝিনাইদহ সদর'],
                        ['name' => 'Harinakunda', 'name_bn' => 'হরিণাকুন্ডু'],
                        ['name' => 'Kaliganj', 'name_bn' => 'কালীগঞ্জ'],
                        ['name' => 'Kotchandpur', 'name_bn' => 'কোটচাঁদপুর'],
                        ['name' => 'Maheshpur', 'name_bn' => 'মহেশপুর'],
                        ['name' => 'Shailkupa', 'name_bn' => 'শৈলকুপা'],
                    ]
                ],
                [
                    'district' => 'Khulna',
                    'district_bn' => 'খুলনা',
                    'division' => 'Khulna',
                    'division_bn' => 'খুলনা',
                    'upazillas' => [
                        ['name' => 'Batiaghata', 'name_bn' => 'বটিয়াঘাটা'],
                        ['name' => 'Dacope', 'name_bn' => 'ডাকোপ'],
                        ['name' => 'Dumuria', 'name_bn' => 'ডুমুরিয়া'],
                        ['name' => 'Dighalia', 'name_bn' => 'দিঘলিয়া'],
                        ['name' => 'Koyra', 'name_bn' => 'কয়রা'],
                        ['name' => 'Paikgachha', 'name_bn' => 'পাইকগাছা'],
                        ['name' => 'Phultala', 'name_bn' => 'ফুলতলা'],
                        ['name' => 'Rupsa', 'name_bn' => 'রূপসা'],
                        ['name' => 'Terokhada', 'name_bn' => 'তেরখাদা'],
                    ]
                ],
                [
                    'district' => 'Kushtia',
                    'district_bn' => 'কুষ্টিয়া',
                    'division' => 'Khulna',
                    'division_bn' => 'খুলনা',
                    'upazillas' => [
                        ['name' => 'Kushtia Sadar', 'name_bn' => 'কুষ্টিয়া সদর'],
                        ['name' => 'Bheramara', 'name_bn' => 'ভেড়ামারা'],
                        ['name' => 'Daulatpur', 'name_bn' => 'দৌলতপুর'],
                        ['name' => 'Khoksa', 'name_bn' => 'খোকসা'],
                        ['name' => 'Kumarkhali', 'name_bn' => 'কুমারখালি'],
                        ['name' => 'Mirpur', 'name_bn' => 'মিরপুর'],
                    ]
                ],
                [
                    'district' => 'Magura',
                    'district_bn' => 'মাগুরা',
                    'division' => 'Khulna',
                    'division_bn' => 'খুলনা',
                    'upazillas' => [
                        ['name' => 'Magura Sadar', 'name_bn' => 'মাগুরা সদর'],
                        ['name' => 'Mohammadpur', 'name_bn' => 'মোহাম্মদপুর'],
                        ['name' => 'Shalikha', 'name_bn' => 'শালিখা'],
                        ['name' => 'Sreepur', 'name_bn' => 'শ্রীপুর'],
                    ]
                ],
                [
                    'district' => 'Meherpur',
                    'district_bn' => 'মেহেরপুর',
                    'division' => 'Khulna',
                    'division_bn' => 'খুলনা',
                    'upazillas' => [
                        ['name' => 'Meherpur Sadar', 'name_bn' => 'মেহেরপুর সদর'],
                        ['name' => 'Gangni', 'name_bn' => 'গাংনী'],
                        ['name' => 'Mujibnagar', 'name_bn' => 'মুজিবনগর'],
                    ]
                ],
                [
                    'district' => 'Narail',
                    'district_bn' => 'নড়াইল',
                    'division' => 'Khulna',
                    'division_bn' => 'খুলনা',
                    'upazillas' => [
                        ['name' => 'Narail Sadar', 'name_bn' => 'নড়াইল সদর'],
                        ['name' => 'Kalia', 'name_bn' => 'কালিয়া'],
                        ['name' => 'Lohagara', 'name_bn' => 'লোহাগড়া'],
                    ]
                ],
                [
                    'district' => 'Satkhira',
                    'district_bn' => 'সাতক্ষীরা',
                    'division' => 'Khulna',
                    'division_bn' => 'খুলনা',
                    'upazillas' => [
                        ['name' => 'Satkhira Sadar', 'name_bn' => 'সাতক্ষীরা সদর'],
                        ['name' => 'Assasuni', 'name_bn' => 'আশাশুনি'],
                        ['name' => 'Debhata', 'name_bn' => 'দেবহাটা'],
                        ['name' => 'Kalaroa', 'name_bn' => 'কলারোয়া'],
                        ['name' => 'Kaliganj', 'name_bn' => 'কালীগঞ্জ'],
                        ['name' => 'Shyamnagar', 'name_bn' => 'শ্যামনগর'],
                        ['name' => 'Tala', 'name_bn' => 'তালা'],
                    ]
                ],

                // Barishal Division
                [
                    'district' => 'Barguna',
                    'district_bn' => 'বরগুনা',
                    'division' => 'Barishal',
                    'division_bn' => 'বরিশাল',
                    'upazillas' => [
                        ['name' => 'Barguna Sadar', 'name_bn' => 'বরগুনা সদর'],
                        ['name' => 'Amtali', 'name_bn' => 'আমতলি'],
                        ['name' => 'Bamna', 'name_bn' => 'বামনা'],
                        ['name' => 'Betagi', 'name_bn' => 'বেতাগি'],
                        ['name' => 'Patharghata', 'name_bn' => 'পাথরঘাটা'],
                        ['name' => 'Taltali', 'name_bn' => 'তালতলি'],
                    ]
                ],
                [
                    'district' => 'Barishal',
                    'district_bn' => 'বরিশাল',
                    'division' => 'Barishal',
                    'division_bn' => 'বরিশাল',
                    'upazillas' => [
                        ['name' => 'Barishal Sadar', 'name_bn' => 'বরিশাল সদর'],
                        ['name' => 'Agailjhara', 'name_bn' => 'আগৈলঝাড়া'],
                        ['name' => 'Babuganj', 'name_bn' => 'বাবুগঞ্জ'],
                        ['name' => 'Bakerganj', 'name_bn' => 'বাকেরগঞ্জ'],
                        ['name' => 'Banaripara', 'name_bn' => 'বানারিপাড়া'],
                        ['name' => 'Gaurnadi', 'name_bn' => 'গৌরনদী'],
                        ['name' => 'Hizla', 'name_bn' => 'হিজলা'],
                        ['name' => 'Mehendiganj', 'name_bn' => 'মেহেন্দিগঞ্জ'],
                        ['name' => 'Muladi', 'name_bn' => 'মুলাদি'],
                        ['name' => 'Wazirpur', 'name_bn' => 'উজিরপুর'],
                    ]
                ],
                [
                    'district' => 'Bhola',
                    'district_bn' => 'ভোলা',
                    'division' => 'Barishal',
                    'division_bn' => 'বরিশাল',
                    'upazillas' => [
                        ['name' => 'Bhola Sadar', 'name_bn' => 'ভোলা সদর'],
                        ['name' => 'Burhanuddin', 'name_bn' => 'বুরহানউদ্দিন'],
                        ['name' => 'Char Fasson', 'name_bn' => 'চর ফ্যাশন'],
                        ['name' => 'Daulatkhan', 'name_bn' => 'দৌলতখান'],
                        ['name' => 'Lalmohan', 'name_bn' => 'লালমোহন'],
                        ['name' => 'Manpura', 'name_bn' => 'মনপুরা'],
                        ['name' => 'Tazumuddin', 'name_bn' => 'তজুমদ্দিন'],
                    ]
                ],
                [
                    'district' => 'Jhalokathi',
                    'district_bn' => 'ঝালকাঠি',
                    'division' => 'Barishal',
                    'division_bn' => 'বরিশাল',
                    'upazillas' => [
                        ['name' => 'Jhalokathi Sadar', 'name_bn' => 'ঝালকাঠি সদর'],
                        ['name' => 'Kathalia', 'name_bn' => 'কাঠালিয়া'],
                        ['name' => 'Nalchity', 'name_bn' => 'নলছিটি'],
                        ['name' => 'Rajapur', 'name_bn' => 'রাজাপুর'],
                    ]
                ],
                [
                    'district' => 'Patuakhali',
                    'district_bn' => 'পটুয়াখালী',
                    'division' => 'Barishal',
                    'division_bn' => 'বরিশাল',
                    'upazillas' => [
                        ['name' => 'Patuakhali Sadar', 'name_bn' => 'পটুয়াখালী সদর'],
                        ['name' => 'Bauphal', 'name_bn' => 'বাউফল'],
                        ['name' => 'Dashmina', 'name_bn' => 'দশমিনা'],
                        ['name' => 'Dumki', 'name_bn' => 'দুমকি'],
                        ['name' => 'Galachipa', 'name_bn' => 'গলাচিপা'],
                        ['name' => 'Kalapara', 'name_bn' => 'কলাপাড়া'],
                        ['name' => 'Mirzaganj', 'name_bn' => 'মির্জাগঞ্জ'],
                        ['name' => 'Rangabali', 'name_bn' => 'রাঙ্গাবালি'],
                    ]
                ],
                [
                    'district' => 'Pirojpur',
                    'district_bn' => 'পিরোজপুর',
                    'division' => 'Barishal',
                    'division_bn' => 'বরিশাল',
                    'upazillas' => [
                        ['name' => 'Pirojpur Sadar', 'name_bn' => 'পিরোজপুর সদর'],
                        ['name' => 'Bhandaria', 'name_bn' => 'ভান্ডারিয়া'],
                        ['name' => 'Kawkhali', 'name_bn' => 'কাউখালি'],
                        ['name' => 'Mathbaria', 'name_bn' => 'মঠবাড়িয়া'],
                        ['name' => 'Nazirpur', 'name_bn' => 'নাজিরপুর'],
                        ['name' => 'Nesarabad', 'name_bn' => 'নেসারাবাদ'],
                        ['name' => 'Zianagar', 'name_bn' => 'জিয়ানগর'],
                    ]
                ],

                // Sylhet Division
                [
                    'district' => 'Habiganj',
                    'district_bn' => 'হবিগঞ্জ',
                    'division' => 'Sylhet',
                    'division_bn' => 'সিলেট',
                    'upazillas' => [
                        ['name' => 'Habiganj Sadar', 'name_bn' => 'হবিগঞ্জ সদর'],
                        ['name' => 'Ajmiriganj', 'name_bn' => 'আজমিরীগঞ্জ'],
                        ['name' => 'Bahubal', 'name_bn' => 'বাহুবল'],
                        ['name' => 'Baniachong', 'name_bn' => 'বানিয়াচং'],
                        ['name' => 'Chunarughat', 'name_bn' => 'চুনারুঘাট'],
                        ['name' => 'Lakhai', 'name_bn' => 'লাখাই'],
                        ['name' => 'Madhabpur', 'name_bn' => 'মাধবপুর'],
                        ['name' => 'Nabiganj', 'name_bn' => 'নবীগঞ্জ'],
                        ['name' => 'Sayestaganj', 'name_bn' => 'শায়েস্তাগঞ্জ'],
                    ]
                ],
                [
                    'district' => 'Moulvibazar',
                    'district_bn' => 'মৌলভীবাজার',
                    'division' => 'Sylhet',
                    'division_bn' => 'সিলেট',
                    'upazillas' => [
                        ['name' => 'Moulvibazar Sadar', 'name_bn' => 'মৌলভীবাজার সদর'],
                        ['name' => 'Barlekha', 'name_bn' => 'বড়লেখা'],
                        ['name' => 'Juri', 'name_bn' => 'জুড়ি'],
                        ['name' => 'Kamalganj', 'name_bn' => 'কমলগঞ্জ'],
                        ['name' => 'Kulaura', 'name_bn' => 'কুলাউড়া'],
                        ['name' => 'Rajnagar', 'name_bn' => 'রাজনগর'],
                        ['name' => 'Sreemangal', 'name_bn' => 'শ্রীমঙ্গল'],
                    ]
                ],
                [
                    'district' => 'Sunamganj',
                    'district_bn' => 'সুনামগঞ্জ',
                    'division' => 'Sylhet',
                    'division_bn' => 'সিলেট',
                    'upazillas' => [
                        ['name' => 'Sunamganj Sadar', 'name_bn' => 'সুনামগঞ্জ সদর'],
                        ['name' => 'Bishwambarpur', 'name_bn' => 'বিশ্বম্ভরপুর'],
                        ['name' => 'Chhatak', 'name_bn' => 'ছাতক'],
                        ['name' => 'Derai', 'name_bn' => 'দিরাই'],
                        ['name' => 'Dharmapasha', 'name_bn' => 'ধর্মপাশা'],
                        ['name' => 'Dowarabazar', 'name_bn' => 'দোয়ারাবাজার'],
                        ['name' => 'Jagannathpur', 'name_bn' => 'জগন্নাথপুর'],
                        ['name' => 'Jamalganj', 'name_bn' => 'জামালগঞ্জ'],
                        ['name' => 'Sullah', 'name_bn' => 'সুল্লা'],
                        ['name' => 'Tahirpur', 'name_bn' => 'তাহিরপুর'],
                    ]
                ],
                [
                    'district' => 'Sylhet',
                    'district_bn' => 'সিলেট',
                    'division' => 'Sylhet',
                    'division_bn' => 'সিলেট',
                    'upazillas' => [
                        ['name' => 'Sylhet Sadar', 'name_bn' => 'সিলেট সদর'],
                        ['name' => 'Balaganj', 'name_bn' => 'বালাগঞ্জ'],
                        ['name' => 'Beanibazar', 'name_bn' => 'বিয়ানীবাজার'],
                        ['name' => 'Bishwanath', 'name_bn' => 'বিশ্বনাথ'],
                        ['name' => 'Companiganj', 'name_bn' => 'কোম্পানীগঞ্জ'],
                        ['name' => 'Dakshin Surma', 'name_bn' => 'দক্ষিণ সুরমা'],
                        ['name' => 'Fenchuganj', 'name_bn' => 'ফেঞ্চুগঞ্জ'],
                        ['name' => 'Golapganj', 'name_bn' => 'গোলাপগঞ্জ'],
                        ['name' => 'Gowainghat', 'name_bn' => 'গোয়াইনঘাট'],
                        ['name' => 'Jaintiapur', 'name_bn' => 'জৈন্তাপুর'],
                        ['name' => 'Kanaighat', 'name_bn' => 'কানাইঘাট'],
                        ['name' => 'Zakiganj', 'name_bn' => 'জকিগঞ্জ'],
                    ]
                ],

                // Rangpur Division
                [
                    'district' => 'Dinajpur',
                    'district_bn' => 'দিনাজপুর',
                    'division' => 'Rangpur',
                    'division_bn' => 'রংপুর',
                    'upazillas' => [
                        ['name' => 'Dinajpur Sadar', 'name_bn' => 'দিনাজপুর সদর'],
                        ['name' => 'Birampur', 'name_bn' => 'বিরামপুর'],
                        ['name' => 'Birganj', 'name_bn' => 'বীরগঞ্জ'],
                        ['name' => 'Biral', 'name_bn' => 'বিরল'],
                        ['name' => 'Bochaganj', 'name_bn' => 'বোচাগঞ্জ'],
                        ['name' => 'Chirirbandar', 'name_bn' => 'চিরিরবন্দর'],
                        ['name' => 'Fulbari', 'name_bn' => 'ফুলবাড়ি'],
                        ['name' => 'Ghoraghat', 'name_bn' => 'ঘোড়াঘাট'],
                        ['name' => 'Hakimpur', 'name_bn' => 'হাকিমপুর'],
                        ['name' => 'Kaharole', 'name_bn' => 'কাহারোল'],
                        ['name' => 'Khansama', 'name_bn' => 'খানসামা'],
                        ['name' => 'Nawabganj', 'name_bn' => 'নবাবগঞ্জ'],
                        ['name' => 'Parbatipur', 'name_bn' => 'পার্বতীপুর'],
                    ]
                ],
                [
                    'district' => 'Gaibandha',
                    'district_bn' => 'গাইবান্ধা',
                    'division' => 'Rangpur',
                    'division_bn' => 'রংপুর',
                    'upazillas' => [
                        ['name' => 'Gaibandha Sadar', 'name_bn' => 'গাইবান্ধা সদর'],
                        ['name' => 'Fulchhari', 'name_bn' => 'ফুলছড়ি'],
                        ['name' => 'Gobindaganj', 'name_bn' => 'গোবিন্দগঞ্জ'],
                        ['name' => 'Palashbari', 'name_bn' => 'পলাশবাড়ি'],
                        ['name' => 'Sadullapur', 'name_bn' => 'সাদুল্লাপুর'],
                        ['name' => 'Saghata', 'name_bn' => 'সাঘাটা'],
                        ['name' => 'Sundarganj', 'name_bn' => 'সুন্দরগঞ্জ'],
                    ]
                ],
                [
                    'district' => 'Kurigram',
                    'district_bn' => 'কুড়িগ্রাম',
                    'division' => 'Rangpur',
                    'division_bn' => 'রংপুর',
                    'upazillas' => [
                        ['name' => 'Kurigram Sadar', 'name_bn' => 'কুড়িগ্রাম সদর'],
                        ['name' => 'Bhurungamari', 'name_bn' => 'ভুরুঙ্গামারি'],
                        ['name' => 'Char Rajibpur', 'name_bn' => 'চর রাজিবপুর'],
                        ['name' => 'Chilmari', 'name_bn' => 'চিলমারি'],
                        ['name' => 'Phulbari', 'name_bn' => 'ফুলবাড়ি'],
                        ['name' => 'Nageshwari', 'name_bn' => 'নাগেশ্বরী'],
                        ['name' => 'Rajarhat', 'name_bn' => 'রাজারহাট'],
                        ['name' => 'Rowmari', 'name_bn' => 'রৌমারি'],
                        ['name' => 'Ulipur', 'name_bn' => 'উলিপুর'],
                    ]
                ],
                [
                    'district' => 'Lalmonirhat',
                    'district_bn' => 'লালমনিরহাট',
                    'division' => 'Rangpur',
                    'division_bn' => 'রংপুর',
                    'upazillas' => [
                        ['name' => 'Lalmonirhat Sadar', 'name_bn' => 'লালমনিরহাট সদর'],
                        ['name' => 'Aditmari', 'name_bn' => 'আদিতমারি'],
                        ['name' => 'Hatibandha', 'name_bn' => 'হাতিবান্ধা'],
                        ['name' => 'Kaliganj', 'name_bn' => 'কালীগঞ্জ'],
                        ['name' => 'Patgram', 'name_bn' => 'পাটগ্রাম'],
                    ]
                ],
                [
                    'district' => 'Nilphamari',
                    'district_bn' => 'নীলফামারী',
                    'division' => 'Rangpur',
                    'division_bn' => 'রংপুর',
                    'upazillas' => [
                        ['name' => 'Nilphamari Sadar', 'name_bn' => 'নীলফামারী সদর'],
                        ['name' => 'Dimla', 'name_bn' => 'ডিমলা'],
                        ['name' => 'Domar', 'name_bn' => 'ডোমার'],
                        ['name' => 'Jaldhaka', 'name_bn' => 'জলঢাকা'],
                        ['name' => 'Kishoreganj', 'name_bn' => 'কিশোরগঞ্জ'],
                        ['name' => 'Saidpur', 'name_bn' => 'সৈয়দপুর'],
                    ]
                ],
                [
                    'district' => 'Panchagarh',
                    'district_bn' => 'পঞ্চগড়',
                    'division' => 'Rangpur',
                    'division_bn' => 'রংপুর',
                    'upazillas' => [
                        ['name' => 'Panchagarh Sadar', 'name_bn' => 'পঞ্চগড় সদর'],
                        ['name' => 'Atwari', 'name_bn' => 'আটোয়ারি'],
                        ['name' => 'Boda', 'name_bn' => 'বোদা'],
                        ['name' => 'Debiganj', 'name_bn' => 'দেবীগঞ্জ'],
                        ['name' => 'Tetulia', 'name_bn' => 'তেতুলিয়া'],
                    ]
                ],
                [
                    'district' => 'Rangpur',
                    'district_bn' => 'রংপুর',
                    'division' => 'Rangpur',
                    'division_bn' => 'রংপুর',
                    'upazillas' => [
                        ['name' => 'Rangpur Sadar', 'name_bn' => 'রংপুর সদর'],
                        ['name' => 'Badarganj', 'name_bn' => 'বদরগঞ্জ'],
                        ['name' => 'Gangachhara', 'name_bn' => 'গঙ্গাচড়া'],
                        ['name' => 'Kaunia', 'name_bn' => 'কাউনিয়া'],
                        ['name' => 'Mithapukur', 'name_bn' => 'মিঠাপুকুর'],
                        ['name' => 'Pirgachha', 'name_bn' => 'পীরগাছা'],
                        ['name' => 'Pirganj', 'name_bn' => 'পীরগঞ্জ'],
                        ['name' => 'Taraganj', 'name_bn' => 'তারাগঞ্জ'],
                    ]
                ],
                [
                    'district' => 'Thakurgaon',
                    'district_bn' => 'ঠাকুরগাঁও',
                    'division' => 'Rangpur',
                    'division_bn' => 'রংপুর',
                    'upazillas' => [
                        ['name' => 'Thakurgaon Sadar', 'name_bn' => 'ঠাকুরগাঁও সদর'],
                        ['name' => 'Baliadangi', 'name_bn' => 'বালিয়াডাঙ্গি'],
                        ['name' => 'Haripur', 'name_bn' => 'হরিপুর'],
                        ['name' => 'Pirganj', 'name_bn' => 'পীরগঞ্জ'],
                        ['name' => 'Ranisankail', 'name_bn' => 'রানীশংকৈল'],
                    ]
                ],

                // Mymensingh Division
                [
                    'district' => 'Jamalpur',
                    'district_bn' => 'জামালপুর',
                    'division' => 'Mymensingh',
                    'division_bn' => 'ময়মনসিংহ',
                    'upazillas' => [
                        ['name' => 'Jamalpur Sadar', 'name_bn' => 'জামালপুর সদর'],
                        ['name' => 'Bakshiganj', 'name_bn' => 'বকশীগঞ্জ'],
                        ['name' => 'Dewanganj', 'name_bn' => 'দেওয়ানগঞ্জ'],
                        ['name' => 'Islampur', 'name_bn' => 'ইসলামপুর'],
                        ['name' => 'Madarganj', 'name_bn' => 'মাদারগঞ্জ'],
                        ['name' => 'Melandaha', 'name_bn' => 'মেলান্দহ'],
                        ['name' => 'Sarishabari', 'name_bn' => 'সরিষাবাড়ি'],
                    ]
                ],
                [
                    'district' => 'Mymensingh',
                    'district_bn' => 'ময়মনসিংহ',
                    'division' => 'Mymensingh',
                    'division_bn' => 'ময়মনসিংহ',
                    'upazillas' => [
                        ['name' => 'Mymensingh Sadar', 'name_bn' => 'ময়মনসিংহ সদর'],
                        ['name' => 'Bhaluka', 'name_bn' => 'ভালুকা'],
                        ['name' => 'Dhobaura', 'name_bn' => 'ধোবাউড়া'],
                        ['name' => 'Fulbaria', 'name_bn' => 'ফুলবাড়িয়া'],
                        ['name' => 'Gaffargaon', 'name_bn' => 'গফরগাঁও'],
                        ['name' => 'Gauripur', 'name_bn' => 'গৌরীপুর'],
                        ['name' => 'Haluaghat', 'name_bn' => 'হালুয়াঘাট'],
                        ['name' => 'Ishwarganj', 'name_bn' => 'ঈশ্বরগঞ্জ'],
                        ['name' => 'Muktagachha', 'name_bn' => 'মুক্তাগাছা'],
                        ['name' => 'Nandail', 'name_bn' => 'নান্দাইল'],
                        ['name' => 'Phulpur', 'name_bn' => 'ফুলপুর'],
                        ['name' => 'Trishal', 'name_bn' => 'ত্রিশাল'],
                    ]
                ],
                [
                    'district' => 'Netrokona',
                    'district_bn' => 'নেত্রকোণা',
                    'division' => 'Mymensingh',
                    'division_bn' => 'ময়মনসিংহ',
                    'upazillas' => [
                        ['name' => 'Netrokona Sadar', 'name_bn' => 'নেত্রকোণা সদর'],
                        ['name' => 'Atpara', 'name_bn' => 'আটপাড়া'],
                        ['name' => 'Barhatta', 'name_bn' => 'বারহাট্টা'],
                        ['name' => 'Durgapur', 'name_bn' => 'দুর্গাপুর'],
                        ['name' => 'Kalmakanda', 'name_bn' => 'কলমাকান্দা'],
                        ['name' => 'Kendua', 'name_bn' => 'কেন্দুয়া'],
                        ['name' => 'Khaliajuri', 'name_bn' => 'খালিয়াজুরি'],
                        ['name' => 'Madan', 'name_bn' => 'মদন'],
                        ['name' => 'Mohanganj', 'name_bn' => 'মোহনগঞ্জ'],
                        ['name' => 'Purbadhala', 'name_bn' => 'পূর্বধলা'],
                    ]
                ],
                [
                    'district' => 'Sherpur',
                    'district_bn' => 'শেরপুর',
                    'division' => 'Mymensingh',
                    'division_bn' => 'ময়মনসিংহ',
                    'upazillas' => [
                        ['name' => 'Sherpur Sadar', 'name_bn' => 'শেরপুর সদর'],
                        ['name' => 'Jhenaigati', 'name_bn' => 'ঝিনাইগাতি'],
                        ['name' => 'Nakla', 'name_bn' => 'নাকলা'],
                        ['name' => 'Nalitabari', 'name_bn' => 'নালিতাবাড়ি'],
                        ['name' => 'Sreebardi', 'name_bn' => 'শ্রীবরদি'],
                    ]
                ],
            ];

            // Insert districts and upazillas
            foreach ($data as $item) {
                $district = District::create([
                    'name' => $item['district'],
                    'name_bn' => $item['district_bn'],
                    'division' => $item['division'],
                    'division_bn' => $item['division_bn'],
                    'is_active' => true,
                    'sort_order' => 0,
                ]);

                foreach ($item['upazillas'] as $upazilla) {
                    Upazila::create([
                        'district_id' => $district->id,
                        'name' => $upazilla['name'],
                        'name_bn' => $upazilla['name_bn'],
                        'is_active' => true,
                        'sort_order' => 0,
                    ]);
                }
            }

            $this->command->info('Districts and Upazillas seeded successfully!');
        });
    }
}

