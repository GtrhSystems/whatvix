<script setup>
import CountUp from 'vue-countup-v3'
defineProps(['counter_section_one'])
</script>

<template>
  <div class="counter-content mt-55 lg-mt-30">
    <div class="container">
      <div class="row justify-content-center">
        <div
          v-for="(item, index) in counter_section_one?.items ?? []"
          :key="index"
          class="col-md-4 col-6"
        >
          <div
            class="counter-box-one mt-30"
            :class="{
              'text-center': index === 1,
              'text-md-end text-center': index === 2
            }"
            data-sal="slide-up"
            :data-sal-duration="index + 100"
          >
            <div class="d-inline-block">
              <div class="main-count font-poppins fw-500">
                <CountUp
                  :start-val="item.number > 500 ? item.number - 100 : 0"
                  :end-val="item.number"
                  :options="{
                    enableScrollSpy: true,
                    decimalPlaces: item.number % 2 === 0 ? 0 : 1,
                    scrollSpyDelay: 100,
                    scrollSpyOnce: true,
                    useEasing: true,
                    useGrouping: true,
                    separator: ',',
                    decimal: '.',
                    prefix: '',
                    suffix: item?.suffix
                  }"
                />
              </div>
              <p>{{ item?.subtitle }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
