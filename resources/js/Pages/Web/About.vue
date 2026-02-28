<script setup>
import AppIntegrationThree from '@/Components/Web/AppIntegrationThree.vue'
import BlockFeatureFourteen from '@/Components/Web/BlockFeatureFourteen.vue'
import BlockFeatureOne from '@/Components/Web/BlockFeatureOne.vue'
import BlogSectionOne from '@/Components/Web/BlogSectionOne.vue'
import CounterSectionOne from '@/Components/Web/CounterSectionOne.vue'
import FancyBannerOne from '@/Components/Web/FancyBannerOne.vue'
import FeedbackSectionOne from '@/Components/Web/FeedbackSectionOne.vue'
import DefaultLayout from '@/Layouts/Default/DefaultLayout.vue'
defineOptions({ layout: DefaultLayout })
defineProps(['about_page', 'testimonials', 'featured_blogs'])
</script>

<template>
  <BlockFeatureFourteen :block_feature_fourteen="about_page.block_feature_fourteen ?? {}" />
  <CounterSectionOne :counter_section_one="about_page.counter_section_one ?? {}" />
  <BlockFeatureOne :block_feature_one="about_page.block_feature_one ?? {}" />
  <AppIntegrationThree :app_integration_three="about_page.app_integration_three ?? {}" />
  <FeedbackSectionOne
    :feedback_section_one="about_page.feedback_section_one ?? {}"
    :testimonials="testimonials"
    :hidePicture="true"
  />
  <BlogSectionOne
    :blog_section_one="about_page.blog_section_one ?? {}"
    :featured_blogs="featured_blogs"
  />
  <FancyBannerOne :fancy_banner_one="about_page.fancy_banner_one ?? {}" />
</template>
