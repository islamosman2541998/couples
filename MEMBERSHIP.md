# تجربة الاشتراك واكتشاف الألعاب

- الباقة الشاملة: 700 جنيه حسب اختيار مالك المنصة، دفعة واحدة بلا تاريخ انتهاء أو تجديد تلقائي.
- تعديل السعر: لوحة الإدارة ← إعدادات الموقع ← سعر باقة كل الألعاب.
- جميع صفحات اللعب وواجهات الأسئلة تحتاج اشتراكًا موافقًا عليه وغير منتهي وحسابًا نشطًا. الاشتراكات الفردية القديمة تظل صالحة للعبتها.
- خيار «ضمن باقة كل الألعاب فقط» يستخدم الحقل القديم `is_free`؛ لم يعد يتيح اللعب المجاني. إلغاء الخيار وتحديد سعر موجب يتيح بيع اللعبة منفردة.
- سعر الطلب محفوظ في `subscriptions.amount` من بيانات السيرفر، ويظهر للإدارة مع الإيصال.
- محتوى صور الكروت والعجلة في `storage/app/premium`، ويُعرض عبر `games.media` بعد فحص الصلاحية. أغلفة الألعاب التسويقية تظل عامة.
- المعاينات تحتوي نصوصًا تسويقية مستقلة، ولا تحمل الأسئلة الفعلية إلى متصفح الزائر.
- الصفحة الجديدة تحل محل السلايدر والعرض الزمني القديمين. تقييمات العملاء تظل قابلة للإدارة. لا تعرض الصفحة الجديدة إشعارات شراء مولّدة.

## تشغيل نسخة أخرى

1. نشر الملفات والصورة وملفات بناء Vite.
2. تشغيل `php artisan migrate --force` لإضافة بيانات الباقة ونقل الصور القديمة للتخزين الخاص. النقل يتحقق من تطابق النسخة قبل إزالة الملف العام.
3. ضبط سعر الباقة ورقم محفظة فودافون كاش من إعدادات الإدارة. قيمة السعر 700 محفوظة في قاعدة البيانات المحلية الحالية؛ يجب ضبطها أيضًا في أي بيئة أخرى.
4. الطلبات تبدأ بحالة «قيد المراجعة». موافقة الإدارة هي التي تفتح اللعب.

## الصورة المولّدة

- الأداة: أداة ImageGen المدمجة.
- الملف: `public/images/home/date-night-hero.png`.
- Prompt:

Use case: ads-marketing. Asset type: hero illustration for an Arabic couples game subscription website. Create an elegant cinematic 3D still life of mysterious deep purple playing cards with small gold heart and question mark motifs, two ivory dice, a rose pink glass heart, on a midnight plum tabletop. Premium playful date night atmosphere, warm soft light and lavender highlights, tactile paper and glass, refined uncluttered composition. Landscape 3:2. No people, no writing, no logos, no watermark. Objects concentrated in center, generous dark margins.
