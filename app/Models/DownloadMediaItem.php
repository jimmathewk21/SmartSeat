<?php

namespace App\Models;

use App\Traits\Auditable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DownloadMediaItem extends Model
{
    use SoftDeletes, Auditable, HasFactory;

    public $table = 'download_media_items';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'section_title',
        'audience_type',
        'item_title',
        'item_subtitle',
        'image_url',
        'primary_button_text',
        'primary_file_url',
        'secondary_button_text',
        'secondary_file_url',
        'is_grouped_versions',
        'version_1_label',
        'version_1_image_url',
        'version_1_pdf_url',
        'version_1_png_url',
        'version_1_jpg_url',
        'version_2_label',
        'version_2_image_url',
        'version_2_pdf_url',
        'version_2_png_url',
        'version_2_jpg_url',
        'version_3_label',
        'version_3_image_url',
        'version_3_pdf_url',
        'version_3_png_url',
        'version_3_jpg_url',
        'sort_order',
        'is_active',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_grouped_versions' => 'boolean',
    ];

    public const AUDIENCE_SPEAKER = 'speaker';
    public const AUDIENCE_SPONSOR = 'sponsor';
    public const AUDIENCE_DELEGATE = 'delegate';
    public const AUDIENCE_GENERAL = 'general';

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }
}
