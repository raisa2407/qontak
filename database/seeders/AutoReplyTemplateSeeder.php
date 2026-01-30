<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AutoReplyTemplate;

class AutoReplyTemplateSeeder extends Seeder
{
    public function run(): void
    {
        AutoReplyTemplate::updateOrCreate(
            [
                'type' => 'unassigned',
                'keyword' => null,
            ],
            [
                'message' => 'Terima kasih telah menghubungi kami. Kami akan segera melayani anda.',
                'is_active' => true,
            ]
        );

        $assignedTemplates = [
            [
                'keyword' => 'audiensi',
                'message' => 'Untuk mendaftar sebagai audiensi, silahkan kunjungi halaman berikut https://contactmk.mkri.id/id/register/2',
            ],
            [
                'keyword' => 'sidang',
                'message' => 'Untuk permohonan menghadiri sidang, silahkan kunjungi halaman berikut https://contactmk.mkri.id/id/register/3',
            ],
            [
                'keyword' => 'studi',
                'message' => 'Untuk pengajuan studi banding/studi/penelitian, silahkan mengisi permohonan pada laman berikut https://contactmk.mkri.id/id/register/7',
            ],
        ];

        foreach ($assignedTemplates as $template) {
            AutoReplyTemplate::updateOrCreate(
                [
                    'type' => 'assigned',
                    'keyword' => $template['keyword'],
                ],
                [
                    'message' => $template['message'],
                    'is_active' => true,
                ]
            );
        }
    }
}