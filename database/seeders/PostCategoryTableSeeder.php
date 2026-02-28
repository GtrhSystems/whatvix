<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PostCategory;

class PostCategoryTableSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    $post_categories = array(
        array('post_id' => '10','category_id' => '76'),
        array('post_id' => '10','category_id' => '79'),
        array('post_id' => '10','category_id' => '81'),
        array('post_id' => '10','category_id' => '80'),
        array('post_id' => '9','category_id' => '77'),
        array('post_id' => '9','category_id' => '79'),
        array('post_id' => '8','category_id' => '78'),
        array('post_id' => '8','category_id' => '76'),
        array('post_id' => '8','category_id' => '81'),
        array('post_id' => '37','category_id' => '115'),
        array('post_id' => '38','category_id' => '116'),
        array('post_id' => '39','category_id' => '132'),
        array('post_id' => '40','category_id' => '133')
      );

    PostCategory::insert($post_categories);
  }
}
