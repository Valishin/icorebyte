<!DOCTYPE html>
<html lang="{{ $conversation->locale ?? 'es' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        @switch($conversation->locale)
            @case('ca') Nou missatge d'iCOREBYTE @break
            @case('en') New message from iCOREBYTE @break
            @case('fr') Nouveau message d'iCOREBYTE @break
            @default    Nuevo mensaje de iCOREBYTE
        @endswitch
    </title>
    <style>
        body { margin: 0; padding: 0; background: #f8fafc; font-family: 'Helvetica Neue', Arial, sans-serif; }
        .wrap { max-width: 560px; margin: 32px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 16px rgba(0,0,0,.08); }
        .header { background: #006bdf; padding: 28px 32px; }
        .header-title { color: #fff; font-size: 18px; font-weight: 700; margin: 0; }
        .header-sub   { color: rgba(255,255,255,.75); font-size: 13px; margin: 4px 0 0; }
        .body  { padding: 28px 32px; }
        .greeting { font-size: 15px; color: #0f172a; margin: 0 0 16px; }
        .bubble { background: #f1f5f9; border-radius: 12px; padding: 14px 18px; color: #0f172a; font-size: 14px; line-height: 1.6; white-space: pre-wrap; word-break: break-word; }
        .cta-wrap { text-align: center; margin: 28px 0 8px; }
        .cta { display: inline-block; background: #006bdf; color: #fff !important; text-decoration: none; border-radius: 8px; padding: 13px 28px; font-size: 15px; font-weight: 600; }
        .footer { padding: 20px 32px; border-top: 1px solid #e2e8f0; font-size: 12px; color: #94a3b8; text-align: center; }
        .footer a { color: #006bdf; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="header">
        <p class="header-title">Alex · iCOREBYTE</p>
        <p class="header-sub">icorebyte.com</p>
    </div>
    <div class="body">
        @switch($conversation->locale)
            @case('ca')
                <p class="greeting">Hola, {{ $conversation->visitor_name }}!</p>
                @if($message->body)
                <p style="font-size:14px;color:#475569;margin:0 0 10px">T'hem enviat un missatge:</p>
                <div class="bubble">{{ $message->body }}</div>
                @else
                <p style="font-size:14px;color:#475569;margin:0 0 10px">T'hem enviat una imatge.</p>
                @endif
                <div class="cta-wrap"><a class="cta" href="{{ $resumeUrl }}">Veure la conversa</a></div>
                @break

            @case('en')
                <p class="greeting">Hello, {{ $conversation->visitor_name }}!</p>
                @if($message->body)
                <p style="font-size:14px;color:#475569;margin:0 0 10px">We sent you a message:</p>
                <div class="bubble">{{ $message->body }}</div>
                @else
                <p style="font-size:14px;color:#475569;margin:0 0 10px">We sent you an image.</p>
                @endif
                <div class="cta-wrap"><a class="cta" href="{{ $resumeUrl }}">View the conversation</a></div>
                @break

            @case('fr')
                <p class="greeting">Bonjour, {{ $conversation->visitor_name }} !</p>
                @if($message->body)
                <p style="font-size:14px;color:#475569;margin:0 0 10px">Nous vous avons envoyé un message :</p>
                <div class="bubble">{{ $message->body }}</div>
                @else
                <p style="font-size:14px;color:#475569;margin:0 0 10px">Nous vous avons envoyé une image.</p>
                @endif
                <div class="cta-wrap"><a class="cta" href="{{ $resumeUrl }}">Voir la conversation</a></div>
                @break

            @default
                <p class="greeting">Hola, {{ $conversation->visitor_name }}!</p>
                @if($message->body)
                <p style="font-size:14px;color:#475569;margin:0 0 10px">Te hemos enviado un mensaje:</p>
                <div class="bubble">{{ $message->body }}</div>
                @else
                <p style="font-size:14px;color:#475569;margin:0 0 10px">Te hemos enviado una imagen.</p>
                @endif
                <div class="cta-wrap"><a class="cta" href="{{ $resumeUrl }}">Ver la conversación</a></div>
        @endswitch
    </div>
    <div class="footer">
        <p>iCOREBYTE · Carretera de Palamós 57, 17220 Sant Feliu de Guíxols</p>
        <p>Si no quieres recibir estos avisos, simplemente ignora este correo.</p>
    </div>
</div>
</body>
</html>
