<?php

namespace App\Helpers;

class ChineseTranslationHelper
{
    /**
     * Common Chinese to English translations for product properties
     */
    private static array $translations = [
        // Colors
        '黑色' => 'Black',
        '白色' => 'White',
        '红色' => 'Red',
        '蓝色' => 'Blue',
        '绿色' => 'Green',
        '黄色' => 'Yellow',
        '粉色' => 'Pink',
        '紫色' => 'Purple',
        '灰色' => 'Gray',
        '橙色' => 'Orange',
        '棕色' => 'Brown',
        '金色' => 'Gold',
        '银色' => 'Silver',
        '深蓝色' => 'Dark Blue',
        '浅蓝色' => 'Light Blue',
        '深灰色' => 'Dark Gray',
        '浅灰色' => 'Light Gray',
        '深棕色' => 'Dark Brown',
        '米色' => 'Beige',
        '卡其色' => 'Khaki',
        '深棕色' => 'Dark Brown',
        '深绿色' => 'Dark Green',
        '军绿色' => 'Army Green',
        '天蓝色' => 'Sky Blue',
        '玫瑰红' => 'Rose Red',
        '酒红色' => 'Wine Red',
        '深红色' => 'Dark Red',
        '浅粉色' => 'Light Pink',
        '深粉色' => 'Dark Pink',
        
        // Sizes
        'S' => 'S',
        'M' => 'M',
        'L' => 'L',
        'XL' => 'XL',
        'XXL' => 'XXL',
        'XXXL' => 'XXXL',
        '均码' => 'One Size',
        '加大' => 'Plus Size',
        '特大' => 'Extra Large',
        
        // Common values
        '是' => 'Yes',
        '否' => 'No',
        '有' => 'Yes',
        '无' => 'No',
        '其他' => 'Other',
        '其它' => 'Other',
        '成人' => 'Adult',
        '儿童' => 'Children',
        '男' => 'Male',
        '女' => 'Female',
        '通用' => 'Unisex',
        '中国大陆' => 'Mainland China',
        '香港' => 'Hong Kong',
        '台湾' => 'Taiwan',
        
        // Materials
        '棉' => 'Cotton',
        '涤纶' => 'Polyester',
        '尼龙' => 'Nylon',
        '皮革' => 'Leather',
        '丝绸' => 'Silk',
        '羊毛' => 'Wool',
        '麻' => 'Linen',
        '金属' => 'Metal',
        '塑料' => 'Plastic',
        '木' => 'Wood',
        '玻璃' => 'Glass',
        '橡胶' => 'Rubber',
        '硅胶' => 'Silicone',
        
        // Common property names
        '颜色' => 'Color',
        '尺寸' => 'Size',
        '材质' => 'Material',
        '品牌' => 'Brand',
        '型号' => 'Model',
        '重量' => 'Weight',
        '产地' => 'Origin',
        '包装' => 'Package',
        '功能' => 'Function',
        '适用人群' => 'Suitable For',
        '风格' => 'Style',
        '款式' => 'Style',
        '图案' => 'Pattern',
        '版本' => 'Version',
        '容量' => 'Capacity',
        '数量' => 'Quantity',
        '通讯类型' => 'Communication Type',
        '充电方式' => 'Charging Method',
        '屏幕类型' => 'Screen Type',
        '防水等级' => 'Waterproof Level',
        '续航时间' => 'Battery Life',
        '表壳材质' => 'Case Material',
        '上市时间' => 'Release Time',
        '操作方式' => 'Operation Method',
        '腕带材质' => 'Strap Material',
        '产品重量' => 'Product Weight',
        '售后服务' => 'After-sales Service',
        '包装清单' => 'Package List',
        '版本类型' => 'Version Type',
        '表带款式' => 'Strap Style',
        '兼容平台' => 'Compatible Platform',
        '主要下游平台' => 'Main Downstream Platform',
        '主要销售地区' => 'Main Sales Region',
        '是否跨境出口专供货源' => 'Cross-border Export',
        '是否专利货源' => 'Patent Source',
        '模具类型' => 'Mold Type',
        '表盘款式' => 'Dial Style',
        '无线电发射设备型号核准编码' => 'Radio Equipment Approval Code',
        '支持订制' => 'Customization Support',

        // Common values
        '蓝牙' => 'Bluetooth',
        '蓝牙通话' => 'Bluetooth Call',
        '智能提醒' => 'Smart Reminder',
        '无线充电' => 'Wireless Charging',
        '不防水' => 'Not Waterproof',
        '7天以下' => 'Less than 7 days',
        '触摸式' => 'Touch',
        '按键式' => 'Button',
        '触摸式+按键式' => 'Touch + Button',
        '店面三包' => 'Store Three Guarantees',
        '产品、说明书、数据线' => 'Product, Manual, Data Cable',
        '经典扣式' => 'Classic Buckle',
        '方形' => 'Square',
        '圆形' => 'Round',
        '私模' => 'Private Mold',
        '公模' => 'Public Mold',
        '速卖通' => 'AliExpress',
        '独立站' => 'Independent Site',
        '南美' => 'South America',
        '东南亚' => 'Southeast Asia',
        '北美' => 'North America',
        '中东' => 'Middle East',
        '欧洲' => 'Europe',
        '非洲' => 'Africa',
        '大洋洲' => 'Oceania',
        '有限公司' => 'Co., Ltd.',
        '科技' => 'Technology',
        '深圳市' => 'Shenzhen',
        '广州市' => 'Guangzhou',
        '北京市' => 'Beijing',
        '上海市' => 'Shanghai',
        '屏幕' => ' Screen',
        '通话' => 'Call',
        '提醒' => 'Reminder',
        '充电' => 'Charging',
        '无线' => 'Wireless',
        '艾诺麦克斯' => 'Himacom',
        '市' => '',
        '省' => '',
    ];

    /**
     * Translate Chinese text to English
     * Returns original text if no translation found
     */
    public static function translate(string $text): string
    {
        // Ensure the input is valid UTF-8
        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        }

        // Return as-is if already in English (contains only ASCII characters)
        if (preg_match('/^[\x20-\x7E]*$/', $text)) {
            return $text;
        }

        // Check if we have a direct translation
        if (isset(self::$translations[$text])) {
            return self::$translations[$text];
        }

        // Handle comma-separated values (e.g., "蓝牙通话,智能提醒")
        if (mb_strpos($text, ',') !== false || mb_strpos($text, '，') !== false) {
            $parts = preg_split('/[,，]/u', $text);
            $translatedParts = [];
            foreach ($parts as $part) {
                $trimmed = trim($part);
                if ($trimmed !== '') {
                    $translatedParts[] = self::translateSingle($trimmed);
                }
            }
            return implode(', ', $translatedParts);
        }

        return self::translateSingle($text);
    }

    /**
     * Translate a single text without comma handling
     */
    private static function translateSingle(string $text): string
    {
        // Check if we have a direct translation
        if (isset(self::$translations[$text])) {
            return self::$translations[$text];
        }

        // Try to translate parts of the text (e.g., "Color:黑色" -> "Color:Black")
        // Sort by length (longest first) to avoid partial replacements
        static $sortedTranslations = null;
        if ($sortedTranslations === null) {
            $sortedTranslations = self::$translations;
            uksort($sortedTranslations, function($a, $b) {
                return mb_strlen($b, 'UTF-8') - mb_strlen($a, 'UTF-8');
            });
        }

        $translated = $text;
        foreach ($sortedTranslations as $chinese => $english) {
            $translated = str_replace($chinese, $english, $translated);
        }

        // Ensure the output is valid UTF-8
        if (!mb_check_encoding($translated, 'UTF-8')) {
            $translated = mb_convert_encoding($translated, 'UTF-8', 'UTF-8');
        }

        return $translated;
    }

    /**
     * Translate an array of properties
     */
    public static function translateProperties(array $props): array
    {
        $translated = [];
        foreach ($props as $key => $value) {
            $translatedKey = self::translate($key);
            $translatedValue = is_string($value) ? self::translate($value) : $value;
            $translated[$translatedKey] = $translatedValue;
        }
        return $translated;
    }

    /**
     * Translate props_names format (e.g., "Color:黑色;Size:L")
     */
    public static function translatePropsNames(string $propsNames): string
    {
        // Split by semicolon for multiple properties
        $parts = explode(';', $propsNames);
        $translatedParts = [];

        foreach ($parts as $part) {
            // Split by colon for key:value pairs
            if (strpos($part, ':') !== false) {
                list($key, $value) = explode(':', $part, 2);
                $translatedKey = self::translate(trim($key));
                $translatedValue = self::translate(trim($value));
                $translatedParts[] = $translatedKey . ':' . $translatedValue;
            } else {
                $translatedParts[] = self::translate(trim($part));
            }
        }

        return implode(';', $translatedParts);
    }
}

