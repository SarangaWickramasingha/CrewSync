<?php
// Whitelist-based HTML sanitizer for notification messages.
// Notifications may contain trusted inline formatting (<strong>, <a href="...">),
// so we strip / neutralize everything else instead of encoding the whole message.
// NOTE: this is NOT a general-purpose sanitizer; only use it on notification text.
function sanitize_notification_html($html) {
    if ($html === null) return '';
    $html = (string) $html;

    // Remove comments, CDATA, and PHP tags.
    $html = preg_replace('/<!--.*?-->/s', '', $html);
    $html = preg_replace('/<!\[CDATA\[.*?\]\]>/is', '', $html);
    $html = preg_replace('/<\?(?:php)?.*?\?>/is', '', $html);

    // Remove whole dangerous blocks (including their content).
    $html = preg_replace(
        '/<(script|style|iframe|object|embed|form|link|meta|base|svg|math|template|audio|video)\b[^>]*>.*?<\/\1>/is',
        '',
        $html
    );
    // Drop any leftover self-closing / void variants of the dangerous tags.
    $html = preg_replace('/<(script|style|iframe|object|embed|form|link|meta|base|svg|math|template|audio|video)\b[^>]*\/?>/is', '', $html);

    // Strip event-handler attributes (onclick, onerror, ...).
    $html = preg_replace('/\s+on[a-z]+\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);

    // Neutralize javascript:/data:/vbscript: hrefs.
    $html = preg_replace(
        '/\s+href\s*=\s*(?:"\s*(?:javascript|data|vbscript):[^"]*"|\'\s*(?:javascript|data|vbscript):[^\']*\'|(?:javascript|data|vbscript):[^\s>]+)/i',
        ' href="#"',
        $html
    );
    $html = preg_replace(
        '/\s+src\s*=\s*(?:"\s*(?:javascript|data):[^"]*"|\'\s*(?:javascript|data):[^\']*\'|(?:javascript|data):[^\s>]+)/i',
        '',
        $html
    );

    // Remove everything that is not in the inline-formatting whitelist.
    $html = strip_tags($html, '<strong><b><em><i><u><span><a><br>');

    // Rebuild each <a>...</a> element keeping only a safe href; invalid or
    // missing targets degrade to their inner text (unmatched anchors are
    // already neutralized to href="#" by the checks above).
    $html = preg_replace_callback(
        '/<a\b([^>]*)>(.*?)<\/a>/is',
        function ($matches) {
            $inner = $matches[2];
            $href  = '';
            if (preg_match('/href\s*=\s*(?:"([^"]*)"|\'([^\']*)\')/i', $matches[1], $hm)) {
                $candidate = trim(!empty($hm[2]) ? $hm[2] : $hm[1]);
                if (
                    $candidate !== '' &&
                    !preg_match('#[\s\\\\]#', $candidate) &&
                    (preg_match('#^/#', $candidate) || preg_match('#^https?://#i', $candidate))
                ) {
                    $href = $candidate;
                }
            }
            if ($href === '') {
                return $inner;
            }
            return '<a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '" target="_blank" rel="noopener noreferrer">' . $inner . '</a>';
        },
        $html
    );

    // Strip any attributes that could remain on the other allowed tags.
    $html = preg_replace('/<(strong|b|em|i|u|span|br)\b[^>]*>/i', '<$1>', $html);

    return $html;
}