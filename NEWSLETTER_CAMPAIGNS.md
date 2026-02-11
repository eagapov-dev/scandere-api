# Newsletter Campaigns - Manual Sending Guide

## Overview

Система позволяет отправлять ручные email рассылки всем активным подписчикам. Все email автоматически включают unsubscribe ссылку (CAN-SPAM compliance).

## Способы отправки

### 1. Через Admin UI (Рекомендуется) 🎨

**Самый простой способ!**

1. Откройте админ-панель: `/admin/newsletter`
2. Заполните форму:
   - Email Subject
   - Message Content
3. Нажмите **Preview** для предпросмотра (опционально)
4. Нажмите **Send Campaign**
5. Подтвердите отправку

**Преимущества:**
- ✅ Визуальный интерфейс
- ✅ Предпросмотр перед отправкой
- ✅ Статистика подписчиков
- ✅ Валидация формы
- ✅ Best practices подсказки
- ✅ Не нужно знать технические детали

**Документация:** См. `scandere-frontend/NEWSLETTER_ADMIN_GUIDE.md`

### 2. Через API (для интеграций)

**Endpoint:**
```
POST /api/admin/newsletter/send
```

**Headers:**
```
Authorization: Bearer {admin_token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "subject": "New AI Tools Released!",
  "content": "We're excited to announce new AI tools available in our store.\n\nCheck out our latest products and get 20% off your first purchase.\n\nVisit our store today!"
}
```

**Response:**
```json
{
  "message": "Newsletter campaign queued for 150 subscribers",
  "subscribers_count": 150
}
```

**Validation:**
- `subject` - required, max 255 символов
- `content` - required, max 10,000 символов
- Поддерживает переносы строк (`\n`)

### 2. Через Artisan команду

**Базовое использование:**

```bash
# С текстом в параметре
php artisan newsletter:send "Subject Line" --message="Your newsletter content here"

# Из файла
php artisan newsletter:send "Subject Line" --file=/path/to/message.txt
```

**Примеры:**

```bash
# Простое сообщение
php artisan newsletter:send "New Products Alert" \
  --message="Check out our new AI tools! Visit scandereai.store"

# Из текстового файла
echo "Hello subscribers!

We have exciting news to share...

Best regards,
Scandere AI Team" > message.txt

php artisan newsletter:send "Monthly Newsletter" --file=message.txt
```

**Процесс выполнения:**
1. Команда найдёт всех активных подписчиков
2. Покажет количество и спросит подтверждение
3. Поставит все email в очередь
4. Покажет прогресс бар

**Интерактивный пример:**

```
$ php artisan newsletter:send "February Update" --file=newsletter.txt

Found 150 active subscribers

 Do you want to send the newsletter campaign? (yes/no) [yes]:
 > yes

Queueing emails...
 150/150 [============================] 100%

✓ Newsletter campaign queued for 150 subscribers
Emails will be sent by the queue worker
```

## Проверка статистики подписчиков

**Endpoint:**
```
GET /api/admin/newsletter/stats
```

**Response:**
```json
{
  "total_subscribers": 250,
  "active_subscribers": 200,
  "unsubscribed": 50
}
```

## Email Template

Каждая рассылка использует template: `resources/views/emails/newsletter/campaign.blade.php`

**Структура email:**
- Ваше сообщение (с переносами строк)
- Разделитель
- Кнопка "Browse Our Products"
- Подпись "Scandere AI Team"
- Footer с unsubscribe ссылкой

**Unsubscribe ссылка автоматически добавляется:**
```
{frontend_url}/unsubscribe/{email}
```

## Процесс отправки

1. **Queue System** - все emails ставятся в очередь (не отправляются сразу)
2. **Background Processing** - queue worker обрабатывает по 1 email за раз
3. **Retry Logic** - 3 попытки отправки с интервалами 1min, 5min, 15min
4. **Error Handling** - failed emails логируются в `failed_jobs` table

## Monitoring

**Проверить очередь:**
```bash
# Количество jobs в очереди
php artisan tinker
>>> DB::table('jobs')->count()

# Просмотр failed jobs
php artisan queue:failed

# Retry failed
php artisan queue:retry all
```

**Логи:**
```bash
tail -f storage/logs/laravel.log | grep -i mail
```

## Best Practices

### Содержание рассылки

✅ **DO:**
- Краткое и ясное сообщение
- Call-to-action (призыв к действию)
- Персонализация (если возможно)
- Ценная информация для подписчиков

❌ **DON'T:**
- Слишком длинные тексты
- Спам-слова (FREE!!!, BUY NOW!!!)
- Слишком частые рассылки
- Caps Lock в заголовках

### Timing

- **Лучшее время:** Вторник-четверг, 10:00-14:00
- **Избегайте:** Понедельник утро, пятница вечер, выходные
- **Частота:** Не чаще 1 раза в неделю

### Testing

**Перед массовой отправкой:**

1. **Отправьте себе тест:**
```bash
php artisan tinker
>>> Mail::to('your-email@example.com')->queue(new \App\Mail\NewsletterCampaign('Test Subject', 'Test content', 'your-email@example.com'));
```

2. **Проверьте:**
   - Тема письма
   - Форматирование текста
   - Работает ли кнопка
   - Работает unsubscribe ссылка
   - Responsive на мобильных
   - Не попадает в спам

3. **Отправьте небольшой тестовой группе** (например, 5-10 подписчиков)

## Examples

### Пример 1: Анонс новых продуктов

**Subject:** "New AI Tools Just Launched 🚀"

**Content:**
```
Hi there!

We're excited to announce 3 new AI tools in our store:

🤖 AI Content Generator Pro
📊 Data Analysis Assistant
🎨 Image Enhancement Suite

Get 25% off any new product with code: NEWTOOLS25
Valid until Friday!

Browse new products in our store.

Happy creating!
```

### Пример 2: Monthly Newsletter

**Subject:** "February Newsletter - AI Updates & Tips"

**Content:**
```
Hello Scandere AI Community!

Here's what's new this month:

📰 LATEST NEWS
- 5 new products added
- Updated pricing on premium tools
- New tutorial videos available

💡 AI TIP OF THE MONTH
Use batch processing to save time when working with multiple files.

🎁 SPECIAL OFFER
Premium members get 30% off all products this week.

Thank you for being part of our community!
```

### Пример 3: Special Promotion

**Subject:** "Flash Sale: 40% Off Everything - 24 Hours Only"

**Content:**
```
⚡ FLASH SALE ALERT ⚡

For the next 24 hours, get 40% OFF all AI tools!

Use code: FLASH40 at checkout

Sale ends: Tomorrow at midnight

Don't miss out on this exclusive offer!

Shop now and upgrade your AI toolkit.
```

## Troubleshooting

**Problem:** Subscribers not receiving emails

**Solutions:**
1. Check queue worker is running: `supervisorctl status`
2. Check failed jobs: `php artisan queue:failed`
3. Check Mailgun logs in dashboard
4. Verify subscriber emails are valid

---

**Problem:** Emails going to spam

**Solutions:**
1. Check SPF/DKIM DNS records
2. Avoid spam trigger words
3. Include unsubscribe link (уже есть)
4. Use proper from address
5. Warm up domain (send gradually)

---

**Problem:** "No active subscribers found"

**Solutions:**
1. Check database: `SELECT COUNT(*) FROM subscribers WHERE unsubscribed_at IS NULL`
2. Verify subscribers table has data
3. Check subscription form is working

## API Integration Example (Frontend)

```javascript
// Send newsletter from admin panel
async function sendNewsletter(subject, content) {
  const response = await fetch('/api/admin/newsletter/send', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${adminToken}`
    },
    body: JSON.stringify({ subject, content })
  });

  const data = await response.json();
  console.log(`Queued for ${data.subscribers_count} subscribers`);
  return data;
}

// Get subscriber stats
async function getNewsletterStats() {
  const response = await fetch('/api/admin/newsletter/stats', {
    headers: {
      'Authorization': `Bearer ${adminToken}`
    }
  });

  return await response.json();
}
```

## Security

- ✅ Requires admin authentication
- ✅ Input validation (max lengths)
- ✅ Rate limiting on API endpoints
- ✅ Queue system prevents server overload
- ✅ Automatic unsubscribe links
- ✅ Email content sanitization

## Compliance

**CAN-SPAM Act:**
- ✅ Unsubscribe link in every email
- ✅ Real "from" address
- ✅ Clear subject lines
- ✅ Physical address (in footer)
- ✅ Honor unsubscribe requests immediately

**GDPR:**
- ✅ Opt-in subscription
- ✅ Easy unsubscribe process
- ✅ Data retention policy
- ✅ Subscriber consent tracking

## Files Created

**Mailable:**
- `app/Mail/NewsletterCampaign.php`

**Template:**
- `resources/views/emails/newsletter/campaign.blade.php`

**Controller:**
- `app/Http/Controllers/Admin/NewsletterCampaignController.php`

**Command:**
- `app/Console/Commands/SendNewsletterCampaign.php`

**Routes:**
- `POST /api/admin/newsletter/send`
- `GET /api/admin/newsletter/stats`

---

**Готово к использованию!** 🎉

Для отправки первой рассылки:
1. Убедитесь что queue worker запущен
2. Используйте API endpoint или artisan команду
3. Проверьте статистику подписчиков
4. Отправьте тестовый email себе
5. Запустите кампанию!
