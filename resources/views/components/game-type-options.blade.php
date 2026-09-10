@props(['selected' => 'card'])
@foreach(['card' => '🃏 كروت', 'spinner' => '🎡 سبينر', 'scratch' => '✨ خربش والعب', 'who' => '🤔 مين فيكم؟', 'challenge' => '🎯 تحديات', 'know_me' => '💍 اعرف شريكك', 'control' => '👑 لعبة السيطرة', 'snakes' => '🐍 السلم والتعبان'] as $type => $label)
    <option value="{{ $type }}" @selected($selected === $type)>{{ $label }}</option>
@endforeach
