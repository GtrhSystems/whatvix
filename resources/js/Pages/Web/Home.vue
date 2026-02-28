<script setup>
import DefaultLayout from '@/Layouts/Default/DefaultLayout.vue'
import HeroBanner from '@/Components/Web/HeroBanner.vue'
import PartnerLogo from '@/Components/Web/PartnerLogo.vue'
import FeedbackSectionFour from '@/Components/Web/FeedbackSectionFour.vue'
import FaqSectionOne from '@/Components/Web/FaqSectionOne.vue'
import ApplicationIntegrationTwo from '@/Components/Web/ApplicationIntegrationTwo.vue'
import BlockFeatureEight from '@/Components/Web/BlockFeatureEight.vue'
import BlockFeatureNine from '@/Components/Web/BlockFeatureNine.vue'
import BlockFeatureSeven from '@/Components/Web/BlockFeatureSeven.vue'
import FancyBannerThree from '@/Components/Web/FancyBannerThree.vue'
defineOptions({ layout: DefaultLayout })
defineProps(['home', 'testimonials', 'partner_logos', 'faqs'])
</script>
<template>
  <HeroBanner :hero_banner="home.hero_banner || {}" />
  <BlockFeatureSeven :block_feature_seven="home.block_feature_seven || {}" />
  <BlockFeatureEight :block_feature_eight="home.block_feature_eight || {}" />
  <BlockFeatureNine :block_feature_nine="home.block_feature_nine || {}" />
  <ApplicationIntegrationTwo
    :application_integration_two="home.application_integration_two || {}"
  />
  <FeedbackSectionFour
    :feedback_section_four="home.feedback_section_four || {}"
    :testimonials="testimonials"
  />
  <PartnerLogo :partner_logos="partner_logos || []" />
  <FaqSectionOne :faq_section_one="home.faq_section_one || {}" :faqs="faqs || []" />
  <FancyBannerThree :fancy_banner_three="home.fancy_banner_three || {}" />
</template>
