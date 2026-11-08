<div dir="rtl">

# Karnisha Power By

نشان «طراحی و توسعه با افتخار توسط کارنیشا» برای فوتر سایت‌هایی که کارنیشا طراحی می‌کند — به‌صورت **ویجت المنتور** و **شورت‌کد**.

همه‌ی فایل‌ها (CSS، JS و لوگو) داخل خود پلاگین هستند و از همان سایت لود می‌شوند؛ دیگر هیچ درخواستی به هاست خارجی زده نمی‌شود.

## امکانات

- ویجت المنتور «Karnisha Power By» در دسته‌ی **Karnisha**
- شورت‌کد `[karnisha_powerby]` برای فوترهای غیرالمنتوری
- CSS و JS فقط در صفحه‌هایی لود می‌شوند که نشان در آن‌ها هست؛ اسکریپت `defer` و بدون jQuery است
- باز شدن با **کلیک** یا **هاور** (در موبایل با لمس)
- موقعیت باکس: بالا یا پایین لوگو؛ تراز: ابتدا / وسط / انتها (سازگار با RTL)
- جابه‌جایی خودکار باکس در موبایل تا از صفحه بیرون نزند (جایگزین `left: 80px` قبلی)
- باکس بسته هیچ جایی اشغال نمی‌کند، پس اسکرول افقی در موبایل ایجاد نمی‌کند
- چند نشان در یک صفحه، بسته شدن با کلیک بیرون یا کلید Esc، و دسترس‌پذیری صفحه‌کلید (`button` + `aria-expanded`)
- تنظیمات استایل در المنتور: اندازه لوگو، عرض، رنگ و محوی پس‌زمینه، گردی، سایه، رنگ و تایپوگرافی متن‌ها
- ترجمه‌ی فارسی تنظیمات

## نصب

1. فایل ZIP را از بخش [Releases](https://github.com/karnishaco/karnisha-powerby/releases) دانلود کنید.
2. پیشخوان وردپرس ← افزونه‌ها ← افزودن ← بارگذاری افزونه.
3. فعال کنید و در المنتور ویجت **Karnisha Power By** را در فوتر بگذارید.

### مهاجرت از روش قبلی

در هر سایت این موارد را از هدر/فوتر حذف کنید:

```html
<link rel="stylesheet" href="https://cityseo.ir/cloud/karnisha/powerby/krn-style.css">
<script src="https://cityseo.ir/cloud/karnisha/powerby/krn-script.js"></script>
<div class="krn__powerby" id="krn__powerby"></div>
```

و به‌جایش ویجت یا شورت‌کد را بگذارید. کلاس‌های CSS قبلی (`krn__powerby`، `karnisha__power_box` و…) و کلاس `power_opened` روی `body` حفظ شده‌اند، پس استایل‌های سفارشی قبلی همچنان کار می‌کنند.

## شورت‌کد

```text
[karnisha_powerby]
[karnisha_powerby trigger="hover" position="top" align="center" size="28"]
[karnisha_powerby link="https://example.com" new_tab="yes"]
```

| پارامتر | مقدارها | پیش‌فرض |
|---|---|---|
| `trigger` | `click`، `hover` | `click` |
| `position` | `top`، `bottom` | `top` |
| `align` | `start`، `center`، `end` | `center` |
| `size` | اندازه لوگو به پیکسل | `25` |
| `logo` | آدرس تصویر دلخواه | لوگوی داخل پلاگین |
| `line_1`، `line_2`، `name`، `slogan` | متن | متن‌های کارنیشا |
| `link` | آدرس لینک نام برند | — |
| `new_tab` | `yes`، `no` | `yes` |

## تغییر پیش‌فرض‌ها با کد

```php
add_filter( 'kpb_defaults', function ( $d ) {
	$d['slogan'] = 'کارنیشا، همیشه با شما 😉';
	return $d;
} );
```

## لوگو

لوگو در `assets/images/logo.webp` قرار می‌گیرد (`logo.png` و `logo.svg` هم پشتیبانی می‌شوند). اگر فایل نباشد، یک نشان «K» نمایش داده می‌شود.

## انتشار نسخه‌ی جدید

1. شماره نسخه را در `karnisha-powerby.php` (هدر `Version` و ثابت `KPB_VERSION`) و `readme.txt` (`Stable tag`) بالا ببرید و در `CHANGELOG.md` بنویسید.
2. تگ بزنید و در گیت‌هاب Release بسازید (مثلاً `v1.0.1`).
3. GitHub Actions خودش فایل `karnisha-powerby.zip` را می‌سازد و به Release اضافه می‌کند. اگر نسخه‌ی تگ با نسخه‌ی پلاگین یکی نباشد، ساخت متوقف می‌شود.

## نیازمندی‌ها

- وردپرس 5.8+ و PHP 7.4+
- المنتور برای ویجت (بدون المنتور، شورت‌کد کار می‌کند)

## مجوز

GPL-2.0-or-later

</div>
