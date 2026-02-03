<!-- 2 -->

<?php
require 'includes/db.php';

try {
  
    // جدول الاقسام
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100) NOT NULL UNIQUE
        )
    ");
                              //جدول المنتجات 
    $pdo->exec("
       CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    price DECIMAL(10,2) NOT NULL,
    img VARCHAR(255),
    category_id INT,
    description TEXT,
    features TEXT,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

    ");
               //جدول الطالبات 
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_name VARCHAR(255) NOT NULL,
            customer_phone VARCHAR(20) NOT NULL,
            customer_address TEXT NOT NULL,
            payment_method VARCHAR(50) NOT NULL,
            total DECIMAL(10,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    // جدول تفاصيل الطالب

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT,
            product_id INT,
            quantity INT NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id),
            FOREIGN KEY (product_id) REFERENCES products(id)
        )
    ");

    // جدول المستخدمين
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            role ENUM('user', 'admin') DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // إدراج الفئات
    

    // إدراج الفئات
    $categories = [
        ['name' => 'هواتف', 'slug' => 'phones'],
        ['name' => 'لابتوبات', 'slug' => 'laptops'],
        ['name' => 'أجهزة لوحية', 'slug' => 'tablets']
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO categories (name, slug) VALUES (?, ?)");
    foreach ($categories as $cat) {
        $stmt->execute([$cat['name'], $cat['slug']]);
    }

    // إدراج المنتجات
    $products = [
        // هواتف
        [1, "أيفون 17 برو ماكس", 1199, "images/iphone17promax.jpg", "أقوى هاتف من أبل بهيكل من التيطانيوم وكاميرا تقريب مذهلة.", "هيكل تيتانيوم صلب,معالج A17 Pro الجبار,كاميرا تقريب 5x,شاشة LTPO Super Retina"],
        [1, "سامسونج جالكسي S24 ألترا", 1299, "images/s24ultra.jpg", "شاشة هي الأفضل عالمياً مع قلم S-Pen مدمج وميزات ذكاء اصطناعي.", "إطار من التيتانيوم,قلم S-Pen مدمج,ذكاء اصطناعي متطور,كاميرا 200 ميجابكسل"],
        [1, "جوجل بكسل 8 برو", 999, "images/pixel8pro.jpg", "هاتف جوجل الذكي الذي يقدم أفضل تجربة تصوير فوتوغرافي بذكاء اصطناعي.", "أفضل معالجة صور ذكية,شاشة Super Actua سطوع عالي,تحديثات نظام لمدة 7 سنوات"],
        [1, "شاومي 13 ألترا", 850, "images/xiaomi13.jpg", "وحش التصوير من شاومي بعدسات لايكا الاحترافية.", "نظام تصوير لايكا الاحترافي,شحن سريع جداً 90 واط,شاشة بدقة 2K"],
        [1, "هونر ماجيك 6 برو", 950, "images/honor_magic6.jpg", "يتميز ببطارية ضخمة وشاشة محمية ضد الكسر بشكل مذهل.", "شاشة حماية نانو كريستال,تصوير رياضي فائق السرعة,بطارية تدوم طويلاً"],
        [1, "ناثينج فون (2)", 599, "images/nothing2.jpg", "هاتف بتصميم شفاف ومميز مع واجهة Glyph الضوئية الفريدة.", "واجهة Glyph ضوئية مبتكرة,نظام Nothing OS السلس,تصميم خلفي شفاف فريد"],
        [1, "سامسونج جالكسي A54", 350, "images/samsung_a54.jpg", "أفضل قيمة مقابل سعر في الفئة المتوسطة مع شاشة Super AMOLED.", "مقاومة للماء والغبار IP67,شاشة Super AMOLED 120Hz,تصميم زجاجي أنيق"],
        [1, "ريدمي نوت 13 برو", 280, "images/redmi_note13.jpg", "هاتف اقتصادي بمواصفات قوية وكاميرا بدقة 200 ميجابكسل.", "كاميرا أساسية 200MP,شحن توربو 67 واط,شاشة AMOLED بألوان غنية"],

        // لابتوبات
        [2, "أبل ماك بوك برو M3", 1599, "images/img.ph/macbook_m3.jpg", "أقوى لابتوب من أبل للمصممين والمبرمجين مع شاشة ريتينا مذهلة.", "معالج Apple M3 الجديد,ذاكرة 16GB RAM,شاشة Liquid Retina XDR,بطارية تصل لـ 22 ساعة"],
        [2, "إتش بي فيكتوس 15", 850, "images/img.ph/hp_victus.jpg", "لابتوب عملي وقوي للألعاب والدراسة من شركة HP العريقة.", "معالج Core i7 الجيل 13,كرت شاشة RTX 4050,شاشة 144Hz سلسة,نظام تبريد متطور"],
        [2, "ديل إكس بي إس 13", 1200, "images/img.ph/dell_xps.jpg", "الأناقة والقوة في جهاز واحد بشاشة بدون حواف من شركة Dell.", "شاشة 4K InfinityEdge,تصميم ألمنيوم بالكامل,خفيف الوزن جداً,بصمة إصبع ذكية"],
        [2, "لينوفو آيديا باد 5", 650, "images/img.ph/lenovo_ideapad.jpg", "خيار ممتاز للطلاب والأعمال المكتبية بأداء مستقر وسعر منافس.", "معالج Ryzen 7,مساحة تخزين 512GB SSD,لوحة مفاتيح مضيئة,شحن سريع"],
        [2, "أسوس زين بوك 14", 950, "images/img.ph/asus_zenbook.jpg", "لابتوب خفيف الوزن جداً مع شاشة OLED من شركة Asus.", "شاشة OLED ألوان حقيقية,نظام صوت Harman Kardon,هيكل معدني متين,بطارية تدوم طويلاً"],
        [2, "أيسر سويفت جو", 700, "images/img.ph/acer_swift.jpg", "أداء سريع وتصميم نحيف يناسب التنقل الكثير.", "معالج إنتل الجيل 13,كاميرا بدقة QHD,دعم WiFi 6E سريع,وزن 1.2 كجم فقط"],
        [2, "مايكروسوفت سيرفس 5", 1100, "images/img.ph/surface_laptop.jpg", "تجربة ويندوز الرسمية مع شاشة تعمل باللمس وتصميم فاخر.", "شاشة PixelSense باللمس,دعم قلم Surface Pen,أمان ويندوز هالو,تصميم عصري"],
        [2, "هواوي ميت بوك D16", 750, "images/img.ph/matebook_d16.jpg", "شاشة كبيرة وأداء قوي مع ميزات الربط الذكي من هواوي.", "شاشة 16 بوصة مريحة للعين,لوحة مفاتيح رقمية كاملة,خاصية العرض على الشاشات,شاحن مدمج وقوي"],

        // أجهزة لوحية
        [3, "أيباد برو M4", 999, "images/tp/ipad_pro.jpg", "أقوى جهاز لوحي من أبل بشاشة OLED ومعالج M4 الجديد.", "شاشة Ultra Retina XDR,معالج M4 الخارق,دعم قلم Apple Pencil Pro,نحافة قياسية"],
        [3, "سامسونج تاب S9 ألترا", 1100, "images/tp/tab.jpg", "شاشة عملاقة مقاومة للماء مع قلم S-Pen مدمج.", "شاشة AMOLED 14.6 بوصة,مقاومة الماء والغبار IP68,معالج Snapdragon 8 Gen 2,قلم S-Pen مدمج"],
        [3, "مايكروسوفت سيرفس برو 9", 950, "images/tp/surface_pro.jpg", "تابلت ونظام كمبيوتر متكامل في جهاز واحد.", "معالج Core i7 الجيل 12,نظام ويندوز 11 كامل,مسند خلفي مدمج,دعم لوحة مفاتيح وسحاب"],
        [3, "أيباد إير (الجيل 6)", 599, "images/tp/ipad_air.jpg", "التوازن المثالي بين الأداء القوي والسعر المتوسط.", "معالج M2 القوي,كاميرا أمامية بالعرض,دعم لوحة مفاتيح ماجيك,ألوان متعددة"],
        [3, "هواوي ميت باد برو 13", 800, "images/tp/matepad_pro.jpg", "شاشة مذهلة وتصميم نحيف جداً للإبداع والعمل.", "شاشة OLED مرنة,شحن لاسلكي سريع,دعم قلم M-Pencil,نظام صوتي بـ 6 سماعات"],
        [3, "شاومي باد 6 برو", 450, "images/tp/xiaomi_pad6.jpg", "أفضل قيمة مقابل سعر مع شاشة 144Hz ومعالج قوي.", "شاشة بدقة 2.8K,شحن سريع 67 واط,جسم معدني بالكامل,أداء ألعاب ممتاز"],
        [3, "سامسونج جالكسي تاب A9", 180, "images/tp/tab_a9.jpg", "جهاز لوحي اقتصادي مثالي للدراسة والمشاهدة.", "شاشة 8.7 بوصة,مكبرات صوت ستيريو,سعر اقتصادي,مثالي للأطفال والدراسة"],
        [3, "أيباد ميني (الجيل 6)", 499, "images/tp/ipad_mini.jpg", "أقوى تابلت صغير الحجم في العالم.", "تصميم صغير وسهل الحمل,يدعم Apple Pencil 2,منفذ USB-C,معالج A15 Bionic"]
    ];

// إدراج المنتجات مع التحقق من التكرار
$stmt_check = $pdo->prepare("SELECT COUNT(*) FROM products WHERE name = ?");
foreach ($products as $prod) {
    // $prod[1] هو اسم المنتج
    $stmt_check->execute([$prod[1]]);
    if ($stmt_check->fetchColumn() == 0) {
        $stmt_insert = $pdo->prepare("INSERT INTO products (category_id, name, price, img, description, features) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_insert->execute($prod);
    }
}

    // إدراج مستخدم إداري افتراضي
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, password, email, role) VALUES (?, ?, ?, ?)");
    $stmt->execute(['admin', $admin_password, 'admin@example.com', 'admin']);

    echo "تم إعداد قاعدة البيانات بنجاح!";

} catch (PDOException $e) {
    echo "خطأ: " . $e->getMessage();
}
?>
