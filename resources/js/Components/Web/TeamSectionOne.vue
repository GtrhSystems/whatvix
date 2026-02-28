<script setup>
defineProps(['teams', 'team_section_one'])
</script>
<template>
  <div class="team-section-one mt-180 lg-mt-150">
    <div class="container">
      <div class="row">
        <div class="col-xl-8 m-auto text-center" data-sal="slide-up" data-sal-duration="800">
          <div class="title-three mb-40">
            <h2>{{ team_section_one.title }}</h2>
          </div>
          <!-- /.title-three -->
          <p class="fs-24">
            {{ team_section_one.subtitle }}
          </p>
        </div>
      </div>

      <div class="row gx-xl-5">
        <div
          v-for="team in teams"
          class="col-md-4"
          :key="team"
          data-sal="slide-up"
          data-sal-duration="800"
        >
          <div class="team-block-one mt-50 lg-mt-30">
            <div class="img-wrapper position-relative z-1">
              <img :src="team.image" alt="" class="w-100 tran3s" />
              <div class="hover-content d-flex align-items-center justify-content-center">
                <ul class="style-none d-flex align-items-center social-icon">
                  <li v-for="(link, platform) in team.socials" :key="platform">
                    <a :href="sanitizeUrl(link)" target="_blank"
                      ><i class="fa-brands" :class="`fa-${platform}`"></i
                    ></a>
                  </li>
                </ul>
              </div>
              <!-- /.hover-content -->
            </div>
            <!-- /.img-wrapper -->
            <div class="mt-30 text-center">
              <h6>{{ team.name }}</h6>
              <span>{{ team.designation }}</span>
            </div>
          </div>
          <!-- /.team-block-one -->
        </div>
        <div class="col-md-4" data-sal="slide-up" data-sal-duration="800" data-sal-delay="100">
          <div class="team-block-one mt-50 lg-mt-30">
            <div class="img-wrapper position-relative z-1">
              <img src="/assets/frontend/images/media/img_54.jpg" alt="" class="w-100 tran3s" />
              <div class="hover-content d-flex align-items-center justify-content-center">
                <ul class="style-none d-flex align-items-center social-icon">
                  <li>
                    <a href="#"><i class="fa-brands fa-facebook"></i></a>
                  </li>
                  <li>
                    <a href="#"><i class="fa-brands fa-twitter"></i></a>
                  </li>
                  <li>
                    <a href="#"><i class="fa-brands fa-instagram"></i></a>
                  </li>
                </ul>
              </div>
              <!-- /.hover-content -->
            </div>
            <!-- /.img-wrapper -->
            <div class="mt-30 text-center">
              <h6>{{ trans('Aslmaia Sanu') }}</h6>
              <span>{{ trans('Analysis') }}</span>
            </div>
          </div>
          <!-- /.team-block-one -->
        </div>
      </div>
    </div>
  </div>
</template>
