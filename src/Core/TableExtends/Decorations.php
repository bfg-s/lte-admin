<?php

namespace LteAdmin\Core\TableExtends;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Lar\Layout\Abstracts\Component;
use Lar\Layout\Tags\SPAN;
use LteAdmin\Components\FieldComponent;
use LteAdmin\Components\FieldMethods;
use LteAdmin\Components\Fields\RatingField;

class Decorations
{
    /**
     * @param $value
     * @param  array  $props
     * @return FieldComponent|FieldMethods|RatingField
     */
    public function rating_stars($value, $props = [])
    {
        return FieldComponent::rating('rating')
            ->only_input()
            ->readonly()
            ->value($value)
            ->sizeXs();
    }

    /**
     * @param $value
     * @param  array  $props
     * @return string
     */
    public function password_stars($value, $props = [])
    {
        if (!$value) {
            return $this->true_data($value);
        }
        $star = $props[0] ?? '•';
        $id = uniqid('password_');
        $id_showed = "showed_{$id}";
        $stars = str_repeat($star, strlen(strip_tags(html_entity_decode($value))));

        return "<span id='{$id}'><i data-click='0> $::hide 1> $::show' data-params='#{$id} && #{$id_showed}' class='fas fa-eye' style='cursor:pointer'></i> {$stars}</span>".
            "<span id='{$id_showed}' style='display:none'><i data-click='0> $::hide 1> $::show' data-params='#{$id_showed} && #{$id}' class='fas fa-eye-slash' style='cursor:pointer'></i> {$value}</span>";
    }

    /**
     * @param $value
     * @return Component|mixed|null
     */
    public function true_data($value)
    {
        $return = SPAN::create(['badge']);

        if (is_null($value) || $value === '') {
            return $return->addClass('badge-dark')->text('NULL');
        } elseif ($value === true) {
            return $return->addClass('badge-success')->text('TRUE');
        } elseif ($value === false) {
            return $return->addClass('badge-danger')->text('FALSE');
        } elseif (is_array($value)) {
            return $return->addClass('badge-info')->text('Array('.count($value).')');
        } elseif ($value instanceof Carbon) {
            return $value->format('Y-m-d H:i:s');
        } else {
            return $value;
        }
    }

    /**
     * @param $value
     * @param  array  $props
     * @return string
     */
    public function uploaded_file($value, $props = [])
    {
        if ($value) {
            if (is_image(public_path($value))) {
                return $this->avatar($value, $props);
            } else {
                return "<span class=\"badge badge-info\" title='{$value}'>".basename($value).'</span>';
            }
        } else {
            return '<span class="badge badge-dark">none</span>';
        }
    }

    /**
     * @param $props
     * @param $value
     * @return string
     */
    public function avatar($value, $props = [])
    {
        $size = $props[0] ?? 30;

        if ($value) {
            if (!preg_match('/^http/', $value)) {
                $value = '/'.trim($value, '/');
            }

            return "<img src=\"{$value}\" data-click='fancy::img' data-params='{$value}' style=\"width:auto;height:auto;max-width:{$size}px;max-height:{$size}px;cursor:pointer\" />";
        } else {
            return '<span class="badge badge-dark">none</span>';
        }
    }

    /**
     * @param $value
     * @param  array  $props
     * @param  Model|null  $model
     * @return string
     */
    public function copied($value, $props = [], Model $model = null)
    {
        if (isset($props[0]) && is_embedded_call($props[0])) {
            $value_for_copy = call_user_func($props[0], $model);
        }

        if (!$value) {
            return $this->true_data($value);
        }

        return "<a href='javascript:void(0)' class='d-none d-sm-inline' title='Copy to clipboard' data-click='doc::informed_pbcopy' data-params='".strip_tags(html_entity_decode($value_for_copy ?? $value))."'><i class='fas fa-copy'></i></a> ".$value;
    }

    /**
     * @param $value
     * @param  array  $props
     * @param  Model|null  $model
     * @return string
     */
    public function copied_right($value, $props = [], Model $model = null)
    {
        if (isset($props[0]) && is_embedded_call($props[0])) {
            $value_for_copy = call_user_func($props[0], $model);
        }

        if (!$value) {
            return $this->true_data($value);
        }

        return $value." <a href='javascript:void(0)' class='d-none d-sm-inline' title='Copy to clipboard' data-click='doc::informed_pbcopy' data-params='".strip_tags(html_entity_decode($value_for_copy ?? $value))."'><i class='fas fa-copy'></i></a>";
    }

    /**
     * @param $value
     * @return string
     */
    public function badge_number($value)
    {
        return $this->badge($value, [$value <= 0 || $value == 0 ? 'danger' : 'success']);
    }

    /**
     * @param $value
     * @param  array  $props
     * @param  Model|null  $model
     * @return string
     */
    public function badge($value, $props = [], Model $model = null)
    {
        $type = $props[0] ?? 'info';
        if (is_embedded_call($type)) {
            $type = call_user_func($type, $model);
        }

        return "<span class=\"badge badge-{$type}\">{$value}</span>";
    }

    /**
     * @param $value
     * @param $props
     * @return string
     */
    public function pill($value, $props = [], Model $model = null)
    {
        $type = $props[0] ?? 'info';
        if (is_embedded_call($type)) {
            $type = call_user_func($type, $model);
        }

        return "<span class=\"badge badge-pill badge-{$type}\">{$value}</span>";
    }

    /**
     * @param $value
     * @return string
     */
    public function yes_no($value)
    {
        return $value ? '<span class="badge badge-success">'.__('lte.yes').'</span>' :
            '<span class="badge badge-danger">'.__('lte.no').'</span>';
    }

    /**
     * @param $value
     * @return string
     */
    public function on_off($value)
    {
        return $value ? '<span class="badge badge-success">'.__('lte.on').'</span>' :
            '<span class="badge badge-danger">'.__('lte.off').'</span>';
    }

    /**
     * @param $value
     * @param  int[]  $props
     * @return string
     */
    public function fa_icon($value, $props = [])
    {
        $size = $props[0] ?? 22;

        return "<i class='{$value}' title='{$value}' style='font-size: {$size}px'></i>";
    }

    /**
     * @param $value
     * @param  array  $props
     * @return string
     */
    public function badge_tags($value, $props = [])
    {
        $c = collect($value);
        $limit = $props[0] ?? 5;

        return '<span class="badge badge-info">'.
            $c->take($limit)->implode('</span> <span class="badge badge-info">').
            '</span>'.($c->count() > $limit ? ' ... <span class="badge badge-warning" title="'.$c->skip($limit)->implode(', ').'">'.($c->count() - $limit).'x</span>' : '');
    }

    /**
     * @param $value
     * @param  array  $props
     * @return string
     */
    public function color_cube($value, $props = [])
    {
        $size = $props[0] ?? 22;

        return "<i class=\"fas fa-square\" title='{$value}' style=\"color: {$value}; font-size: {$size}px\"></i>";
    }
}
