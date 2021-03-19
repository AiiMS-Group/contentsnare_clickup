<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Library\ClickUp\ClickUp;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    const TEMPLATE_TO_LIST_ID = [
        'Custom Website' => 440061,
        'Google My Business' => 440056,
        'Landing Page' => 440378,
        'SEO Content' => 440050,
        'Social Profile' => 15498957,
        'Google / Bing Ads' => 440023,
    ];

    const TYPE_TO_FUNCTION = [
        'url' => 'processUrl',
        'image' => 'processImage',
        'image select' => 'processImage',
        'wysiwyg' => 'processHtml',
        'default' => 'processDefault',
    ];

    public function store(Request $request)
    {
        $clickUpClient = new ClickUp;

        $sections = collect($request->pages)->pluck('sections')->flatten(1);

        $parsedSections = $sections->map(function ($section) {
            $name = $section['name'];
            $parsedFields = collect($section['fields'])->map(function ($field) {
                $type = $field['type'];
                $parseFunction = self::TYPE_TO_FUNCTION[$type] ?? self::TYPE_TO_FUNCTION['default'];

                $name = $field['name'];
                $values = collect($field['values']);

                return $this->$parseFunction($name, $values);
            })->join("\n\n");

            return "**{$name}**\n{$parsedFields}";
        })->join("\n\n");

        $template = $request->request_template_name;
        $listId = self::TEMPLATE_TO_LIST_ID[$template];
        $businessName = $request->client['company_name'];

        $clickUpClient->task->create($listId, [
            'name' => "{$businessName} - {$template}",
            'markdown_description' => $parsedSections,
        ]);
    }

    private function processUrl($name, Collection $values)
    {
        $value = $values->map(fn ($value) => "[{$value}]({$value})")->join("\n");
        return "**{$name}:**\n{$value}";
    }

    private function processImage($name, Collection $values)
    {
        $value = $values->map(function ($value, $index){
            $key = $index + 1;
            return "[Image {$key}]({$value})";
        })->join("\n");
        return "**{$name}:**\n{$value}";
    }

    private function processHtml($name, Collection $values)
    {
        $value = $values->map(function ($value) {
            $string = Str::of($value)
                ->replace('<br>', "\n");
            return strip_tags($string);
        })->join("\n");
        return "**{$name}:**\n{$value}";
    }

    private function processDefault($name, Collection $values)
    {
        $value = $values->map(fn ($value) => str_replace('&amp;', '&', $value))->join(', ');
        return "**{$name}:**\n{$value}";
    }
}
