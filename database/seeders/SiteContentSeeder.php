<?php

namespace Database\Seeders;

use App\Models\SiteContent;
use Illuminate\Database\Seeder;

class SiteContentSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'home' => [
                'badge'            => 'Available for work',
                'headline'         => "Hi, I'm a <span class=\"text-blue-600\">Developer</span>",
                'subheadline'      => 'I build modern web applications with a focus on clean code, great UX, and solid performance.',
                'cta_primary'      => 'View Projects',
                'cta_secondary'    => 'Get In Touch',
                'cta_section_text' => 'Have a project in mind? I\'d love to help you bring it to life.',
            ],
            'about' => [
                'name'         => 'Your Name',
                'role'         => 'Full Stack Developer',
                'bio'          => "I'm a passionate developer who loves building things for the web. With expertise in modern frameworks and a keen eye for design, I craft digital experiences that are both functional and beautiful.\n\nWhen I'm not coding, you'll find me exploring new technologies, contributing to open source, or writing about my experiences.",
                'resume_url'   => '',
                'github_url'   => 'https://github.com/',
                'linkedin_url' => 'https://linkedin.com/in/',
                'twitter_url'  => '',
                'photo'        => '',
            ],
        ];

        foreach ($defaults as $group => $items) {
            foreach ($items as $key => $value) {
                SiteContent::firstOrCreate(
                    ['group' => $group, 'key' => $key],
                    ['value' => $value]
                );
            }
        }
    }
}
