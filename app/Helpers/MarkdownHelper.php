<?php

namespace App\Helpers;

class MarkdownHelper
{
    /**
     * Parse a basic subset of Markdown + strip embedded chart tags.
     */
    public static function parse(string $text): string
    {
        // Remove chart embed tags from displayed text
        $text = preg_replace('/<!--CHART:.*?-->/s', '', $text);

        // Escape HTML
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

        // Bold **text**
        $text = preg_replace('/\*\*(.+?)\*\*/u', '<strong>$1</strong>', $text);
        // Italic *text*
        $text = preg_replace('/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/u', '<em>$1</em>', $text);
        // Inline code `code`
        $text = preg_replace('/`(.+?)`/u', '<code class="bg-slate-100 text-indigo-600 px-1 rounded text-xs">$1</code>', $text);
        // Blockquote > text (after htmlspecialchars, > becomes &gt;)
        $text = preg_replace('/^&gt; (.+)$/mu', '<div class="border-l-4 border-indigo-300 pl-3 text-slate-500 text-xs my-1">$1</div>', $text);
        // Bullet list - item
        $text = preg_replace('/^- (.+)$/mu', '<div class="flex gap-2 my-0.5"><span class="text-indigo-400 mt-0.5">▪</span><span>$1</span></div>', $text);
        // Newlines
        $text = nl2br($text);

        return $text;
    }
}
