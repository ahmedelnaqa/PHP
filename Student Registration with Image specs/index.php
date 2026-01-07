<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام إدخال بيانات الطلاب</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            line-height: 1.6;
            background-color: #f4f4f4;
            color: #333;
            direction: rtl;
        }
        .header {
            background-color: #2c3e50;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            width: 100%;
        }
        .title {
            flex: 7;
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            margin: 0 20px;
        }
        .title a {
            color: white;
            text-decoration: none;
            transition: opacity 0.3s ease;
        }
        .title a:hover {
            opacity: 0.8;
            text-decoration: none;
        }
        .menu {
            flex: 3;
            text-align: left;
        }
        .menu ul {
            list-style: none;
            display: flex;
            justify-content: flex-start;
            padding: 0;
            margin: 0;
        }
        .menu ul li {
            margin-left: 15px;
        }
        .menu ul li a {
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            display: block;
            border: 2px solid transparent;
            border-radius: 25px;
            transition: all 0.3s ease;
            background: linear-gradient(45deg, #3498db, #2980b9);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .menu ul li a:hover {
            background: linear-gradient(45deg, #2980b9, #21618c);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.3);
            border-color: #ccc;
        }
        .menu ul li a:active {
            transform: translateY(1px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .main-content {
            min-height: calc(100vh - 160px);
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 20px;
        }
        .content-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            max-width: 800px;
            width: 90%;
        }
        .welcome-message {
            background-color: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            font-size: 18px;
            line-height: 1.8;
            text-align: right;
            margin-bottom: 20px;
        }
        .footer {
            background-color: #2c3e50;
            color: white;
            padding: 20px 0;
            margin-top: auto;
        }
        .footer-content {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        .footer-part {
            flex: 1;
            min-width: 200px;
            margin: 10px;
            text-align: center;
        }
        .footer-part h4 {
            margin-bottom: 10px;
            border-bottom: 1px solid #34495e;
            padding-bottom: 5px;
        }
        .logo-main {
            display: flex;
            justify-content: center;
            margin: 20px 0;
        }
        .logo-main img {
            max-width: 400px;
            width: 100%;
            height: auto;
        }
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 60px;
        }
        .action-btn {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            text-decoration: none;
            padding: 20px 30px;
            border-radius: 30px;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            border: 3px solid transparent;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(0,0,0,0.2);
            position: relative;
            overflow: hidden;
        }
        .action-btn:hover {
            background: linear-gradient(45deg, #ff5252, #d84315);
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            border-color: #fff;
        }
        .action-btn:active {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }
        .action-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s;
        }
        .action-btn:hover::before {
            left: 100%;
        }
        /* Responsive Design */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                padding: 10px 15px;
            }
            .title, .menu {
                flex: none;
                width: 100%;
                margin: 5px 0;
            }
            .title {
                font-size: 22px;
                text-align: center;
                margin: 10px 0;
            }
            .menu {
                text-align: center;
                margin-bottom: 10px;
            }
            .menu ul {
                justify-content: center;
                flex-direction: row;
            }
            .menu ul li {
                margin: 0 8px;
            }
            .menu ul li a {
                padding: 10px 15px;
                font-size: 14px;
            }
            .logo-main img {
                max-width: 150px;
            }
            .action-buttons {
                flex-direction: column;
                gap: 20px;
            }
            .action-btn {
                padding: 15px 25px;
                font-size: 16px;
                width: 100%;
                max-width: 300px;
            }
            .footer-content {
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="title">
            <a href="index.php">نظام إدخال بيانات الطلاب</a>
        </div>
        <div class="menu">
            <ul>
                <li><a href="form.php">النموذج</a></li>
                <li><a href="crop_image.php">قص الصورة</a></li>
            </ul>
        </div>
    </header>

    <main class="main-content">
        <div class="content-container">
            <!-- Part 1: RISC Logo -->
            <div class="logo-main">
                <img src="RISULogo.png" alt="شعار جامعة RISC">
            </div>

            <!-- Part 2: Descriptive Text -->
            <div class="welcome-message">
                <p>
تسعى جامعة الإسكندرية دائمًا إلى تبني أحدث الأنظمة التقنية لخدمة أبنائها الطلاب. </br>

وفي إطار هذا التطوير، تم التعاقد مع البنك الأهلي المصري لإصدار بطاقة ذكية (فيزا) لكل طالب-كارنية الكلية-، لتكون: </br>
<ul>
<li>
الوسيلة الأساسية للمعاملات المالية داخل الحرم الجامعي.
</li>

<li>
بطاقة تعريف معتمدة.
</li>

<li>
مفتاح المرور عبر البوابات الإلكترونية للجامعة.
</li>
</ul>
 </br>
ولضمان إصدار البطاقة بدقة وسرعة، يُرجى من السادة الطلاب (المنتظمين فقط، ويُستثنى منها الخريجون) تقديم البيانات التالية بشكل دقيق ومراعاة الشروط الفنية المحددة

 </br>
نشكركم على تعاونكم وحرصكم على دقة البيانات، سائلين المولى عز وجل لجميع طلابنا التوفيق والنجاح.
                </p>
            </div>

            <!-- Part 3: Action Buttons -->
            <div class="action-buttons">
                <a href="form.php" class="action-btn">نموذج الطالب</a>
                <a href="crop_image.php" class="action-btn">قص الصورة</a>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="footer-content">
            جميع الحقوق محفوظة © وحدة البحوث والمعلومات - كلية علوم الرياضية للبنين - أبوقير
        </div>
    </footer>
</body>
</html>
