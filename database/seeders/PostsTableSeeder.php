<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Post;

class PostsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

       $posts = array(
        array('id' => '4','title' => 'Darlene Robertson','slug' => 'Product Manager','type' => 'team','status' => '1','featured' => '1','lang' => 'en','created_at' => '2023-03-06 17:53:17','updated_at' => '2023-03-06 17:53:17'),
        array('id' => '5','title' => 'Bessie Cooper','slug' => 'Vp People','type' => 'team','status' => '1','featured' => '1','lang' => 'en','created_at' => '2023-03-06 17:54:06','updated_at' => '2023-03-06 17:54:06'),
        array('id' => '6','title' => 'Eleanor Pena','slug' => 'Head of Design','type' => 'team','status' => '1','featured' => '1','lang' => 'en','created_at' => '2023-03-06 17:54:44','updated_at' => '2023-03-06 17:54:44'),
        array('id' => '7','title' => 'Rhonda Ortiz','slug' => 'Founder & CO','type' => 'team','status' => '1','featured' => '1','lang' => 'en','created_at' => '2023-03-06 17:55:23','updated_at' => '2023-03-06 17:55:23'),
        array('id' => '8','title' => 'Why need AI Chatbot','slug' => 'why-need-ai-chatbot','type' => 'blog','status' => '1','featured' => '1','lang' => 'en','created_at' => '2023-03-06 18:50:45','updated_at' => '2025-06-23 18:29:18'),
        array('id' => '9','title' => 'What is Whatsapp Web API','slug' => 'what-is-whatsapp-web-api','type' => 'blog','status' => '1','featured' => '1','lang' => 'en','created_at' => '2023-03-06 18:57:44','updated_at' => '2025-06-23 18:28:36'),
        array('id' => '10','title' => 'What is Whatsapp Cloud API','slug' => 'what-is-whatsapp-cloud-api','type' => 'blog','status' => '1','featured' => '1','lang' => 'en','created_at' => '2023-03-06 19:00:52','updated_at' => '2025-06-23 18:28:09'),
        array('id' => '17','title' => 'Apply job or hire','slug' => 'apply-job-or-hire','type' => 'feature','status' => '1','featured' => '1','lang' => 'en','created_at' => '2023-03-06 22:24:43','updated_at' => '2023-10-07 09:25:49'),
        array('id' => '18','title' => 'Complete your profile','slug' => 'complete-your-profile','type' => 'feature','status' => '1','featured' => '1','lang' => 'en','created_at' => '2023-03-06 23:15:36','updated_at' => '2023-10-07 09:24:57'),
        array('id' => '19','title' => 'Create Account','slug' => 'create-account','type' => 'feature','status' => '1','featured' => '1','lang' => 'en','created_at' => '2023-03-06 23:32:24','updated_at' => '2023-10-07 09:24:08'),
        array('id' => '26','title' => 'Terms and conditions','slug' => 'terms-and-conditions','type' => 'page','status' => '1','featured' => '1','lang' => 'en','created_at' => '2023-04-09 19:40:59','updated_at' => '2023-04-09 19:40:59'),
        array('id' => '27','title' => 'Privacy Policy','slug' => 'privacy-policy','type' => 'page','status' => '1','featured' => '1','lang' => 'en','created_at' => '2023-10-08 05:55:19','updated_at' => '2023-10-08 05:55:19'),
        array('id' => '30','title' => 'Selma Hardin','slug' => 'Financial Advisor','type' => 'team','status' => '1','featured' => '1','lang' => 'en','created_at' => '2024-04-03 08:52:12','updated_at' => '2024-05-07 17:48:20'),
        array('id' => '31','title' => 'Facebook Data Deletion Instructions','slug' => 'facebook-data-deletion-instructions','type' => 'page','status' => '1','featured' => '1','lang' => 'en','created_at' => '2025-05-26 23:43:20','updated_at' => '2025-05-26 23:43:20'),
        array('id' => '34','title' => 'Sarah M.','slug' => 'Agency Owner','type' => 'testimonial','status' => '1','featured' => '1','lang' => '5','created_at' => '2025-07-15 19:08:48','updated_at' => '2025-07-28 06:22:13'),
        array('id' => '35','title' => 'Rajat K.','slug' => 'EStore Owner','type' => 'testimonial','status' => '1','featured' => '1','lang' => '5','created_at' => '2025-07-15 19:27:06','updated_at' => '2025-07-29 18:28:38'),
        array('id' => '36','title' => 'Emily T.','slug' => 'Product Manager','type' => 'testimonial','status' => '1','featured' => '1','lang' => '5','created_at' => '2025-07-15 19:28:37','updated_at' => '2025-07-29 18:27:58'),
        array('id' => '37','title' => 'What kind of campaigns can I run?','slug' => 'what-kind-of-campaigns-can-i-run','type' => 'faq','status' => '1','featured' => '1','lang' => 'en','created_at' => '2025-07-21 15:08:56','updated_at' => '2025-07-21 15:32:12'),
        array('id' => '38','title' => 'Can I use AI for replies and automation?','slug' => 'can-i-use-ai-for-replies-and-automation','type' => 'faq','status' => '1','featured' => '1','lang' => 'en','created_at' => '2025-07-21 15:09:18','updated_at' => '2025-07-21 15:31:54'),
        array('id' => '39','title' => 'How can I connect my WhatsApp number?','slug' => 'how-can-i-connect-my-whatsapp-number','type' => 'faq','status' => '1','featured' => '1','lang' => 'en','created_at' => '2025-07-21 15:12:40','updated_at' => '2025-07-21 15:31:32'),
        array('id' => '40','title' => 'What is WhatsML and how does it work?','slug' => 'what-is-WhatsML-and-how-does-it-work','type' => 'faq','status' => '1','featured' => '1','lang' => 'en','created_at' => '2025-07-21 15:13:03','updated_at' => '2025-07-21 15:19:18'),
        array('id' => '43','title' => 'Portia Turner','slug' => 'Freelancer','type' => 'testimonial','status' => '1','featured' => '1','lang' => '5','created_at' => '2025-07-23 05:48:15','updated_at' => '2025-07-28 06:20:22')
        );
          
        Post::insert($posts);
    }
}
