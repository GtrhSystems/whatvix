<script setup>
import FancyBannerSix from '@/Components/Web/FancyBannerSix.vue'
import TeamSectionOne from '@/Components/Web/TeamSectionOne.vue'
import DefaultLayout from '@/Layouts/Default/DefaultLayout.vue'

defineOptions({ layout: DefaultLayout })
defineProps(['teams', 'team_page'])
</script>

<template>
  <TeamSectionOne :team_section_one="team_page.list ?? {}" :teams="teams" />
  <FancyBannerSix :fancy_banner_six="team_page.fancy_banner_six ?? {}" />
</template>
