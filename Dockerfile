# 1. استخدام نسخة PHP رسمية مع FPM
FROM php:8.2-fpm

# 2. تحديد مسار العمل داخل الحاوية
WORKDIR /var/www/html

# 3. تثبيت ملحقات النظام الضرورية (System Dependencies)
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libzip-dev \
    libonig-dev

# 4. تثبيت ملحقات PHP اللازمة لـ Laravel و Redis
RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd

# تثبيت إضافة Redis لكي يتمكن Laravel من التواصل مع حاوية redis-server
RUN pecl install redis && docker-php-ext-enable redis

# 5. تثبيت Composer (مدير حزم PHP)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 6. نسخ ملفات المشروع إلى الحاوية
COPY . .

# 7. إعطاء الصلاحيات اللازمة لمجلدات التخزين
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 8. تشغيل المنفذ 8000 (الذي سيستخدمه Nginx للتواصل مع Laravel)
EXPOSE 8000

# 9. أمر التشغيل الافتراضي
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]