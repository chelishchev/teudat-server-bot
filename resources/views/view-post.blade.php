<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta property="article:published_timestamp" content="{{ $post['timestamp'] }}">
    <title>{{ $post['title'] }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            font-size: 2em;
            margin-bottom: 5px;
        }
        .description {
            font-size: 1.2em;
            /*margin-bottom: 20px;*/
        }
        .posted-on {
            font-size: 0.9em;
            color: #777;
            margin-bottom: 40px;
        }
        .posted-on a {
            text-decoration: none;
        }
        .translations {
            /*font-size: 1.2em;*/
            /*padding-top: 20px;*/
        }
        .translation-header {
            /*font-size: 1.2em;*/
            /*font-weight: bold;*/
        }
        .links a {
            font-size: 1.0em;
            text-decoration: none;
        }
        .divider {
            border-bottom: 1px dashed gray;
            margin: 20px 0;
            width: 100%;
        }

    </style>
</head>
<body>
    <article>
        <h1>{{ $post['title'] }}</h1>
        <p class="posted-on">
            <span class="time-place">Posted on: {{ $post['time'] }}</span><br>
            @if (!empty($post['tg_link']))
                <a href="{{ $post['tg_link'] }}" class="tg-link" target="_blank" rel="noopener noreferrer">← to telegram post</a>
            @endif
        </p>
        <hr>
        <p class="translation-header"><b>Original post</b></p>
        <p class="description">{!! nl2br($post['text']) !!}</p>
        <section class="links">
{{--            <a href="{{ $post['link'] }}" class="fb-link" target="_blank" rel="noopener noreferrer">Contact via Whatsapp</a><br>--}}
{{--            <a href="{{ $post['link'] }}" class="fb-link" target="_blank" rel="noopener noreferrer">See FB profile</a><br>--}}
            <a href="{{ $post['link'] }}" class="fb-link" target="_blank" rel="noopener noreferrer">See FB original post</a>
        </section>
    </article>
    <section class="translations">
        @if (!empty($post['english_translation']))
        <hr>
        <div class="english-translation">
            <p class="translation-header"><b>English Translation</b></p>
            <p class="description">{!! nl2br($post['english_translation']) !!}</p>
            <section class="links">
                <a href="{{ $post['link'] }}" class="fb-link" target="_blank" rel="noopener noreferrer">See FB original post</a>
            </section>
        </div>
        @endif
        @if (!empty($post['russian_translation']))
        <hr>
        <div class="russian-translation">
            <p class="translation-header"><b>Русский перевод</b></p>
            <p class="description">{!! nl2br($post['russian_translation']) !!}</p>
            <section class="links">
                <a href="{{ $post['link'] }}" class="fb-link" target="_blank" rel="noopener noreferrer">See FB original post</a>
            </section>
        </div>
        @endif
    </section>
</body>
</html>
