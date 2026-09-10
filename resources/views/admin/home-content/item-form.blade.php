<form method="POST" enctype="multipart/form-data" action="{{ route('admin.home-content.save', array_filter([$type, $item['id'] ?? null])) }}">
    @csrf
    <div class="home-admin-grid">
        <label>{{ $type === 'slides' ? 'عنوان الشريحة / وصف الصورة' : 'اسم العميل' }}<input name="title" value="{{ $item['title'] ?? '' }}" maxlength="150" required></label>
        <label>الترتيب<input type="number" name="sort_order" min="0" max="9999" value="{{ $item['sort_order'] ?? count($items) }}" required></label>
        <label>{{ $type === 'slides' ? 'صورة البانر' : 'صورة محادثة العميل' }} (حتى 5MB)<input type="file" name="image" accept="image/jpeg,image/png,image/webp" @required($type === 'reviews' && !$item)></label>
        @if($type === 'slides')
            <label>صورة الموبايل (اختيارية)<input type="file" name="mobile_image" accept="image/jpeg,image/png,image/webp"></label>
            <label>رابط الشريحة<input name="url" value="{{ $item['url'] ?? '#games' }}" dir="ltr" placeholder="/games/example"></label>
            @if(!empty($item['mobile_image']))<div><img src="{{ $item['mobile_image'] }}" alt="نسخة الموبايل" class="h-20 mb-2"><label><input type="checkbox" name="remove_mobile_image" value="1">حذف نسخة الموبايل</label></div>@endif
        @endif
    </div>
    <div class="item-actions"><label><input type="checkbox" name="enabled" value="1" @checked($item['enabled'] ?? true)>ظاهر على الموقع</label><button type="submit">{{ $item ? 'حفظ التعديل' : 'إضافة' }}</button></div>
</form>
