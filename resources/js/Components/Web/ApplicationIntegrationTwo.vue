<script setup>
defineProps(['application_integration_two'])
</script>

<template>
  <div class="app-integration-two mt-100 lg-mt-80">
    <div class="container">
      <div class="row">
        <div class="col-lg-10 m-auto">
          <div class="title-two mb-30 text-center" data-sal="slide-up" data-sal-duration="900">
            <h2>{{ application_integration_two.title }}</h2>
            <p class="fw-500 fs-24 mt-20">{{ application_integration_two.subtitle }}</p>
          </div>
          <!-- /.title-one -->
        </div>
      </div>

      <div class="main-wrapper position-relative z-1 lg-mt-40">
        <div class="d-flex align-items-center flex-wrap">
          <div class="logo-box position-relative z-1">
            <img
              :src="
                sanitizeHtml(application_integration_two.left_image_one) ??
                '/assets/frontend/images/logo/p_logo_15.png'
              "
              alt=""
              class="logo logo_01"
            />
            <img
              :src="
                sanitizeHtml(application_integration_two.left_image_two) ??
                '/assets/frontend/images/logo/p_logo_16.png'
              "
              alt=""
              class="logo logo_02"
            />
            <img
              :src="
                sanitizeHtml(application_integration_two.left_image_three) ??
                '/assets/frontend/images/logo/p_logo_17.png'
              "
              alt=""
              class="logo logo_03"
            />
            <img
              :src="
                sanitizeHtml(application_integration_two.left_image_four) ??
                '/assets/frontend/images/logo/p_logo_18.png'
              "
              alt=""
              class="logo logo_04"
            />
          </div>
          <!-- /.logo-box -->

          <div class="icon-box position-relative me-auto ms-auto text-center">
            <div class="icon d-flex align-items-center justify-content-center rounded-circle">
              <img
                :src="
                  sanitizeHtml(application_integration_two.center_image) ??
                  '/assets/frontend/images/icon/icon_30.svg'
                "
                alt=""
              />
            </div>
            <div class="text">
              <h6>{{ application_integration_two.center_title }}</h6>
              <span class="font-outfit opacity-50">{{
                application_integration_two.center_subtitle
              }}</span>
            </div>
          </div>
          <!-- /.icon-box -->

          <div class="logo-box position-relative z-1">
            <img
              :src="
                sanitizeHtml(application_integration_two.right_image_one) ??
                '/assets/frontend/images/logo/p_logo_19.png'
              "
              alt=""
              class="logo logo_01"
            />
            <img
              :src="
                sanitizeHtml(application_integration_two.right_image_two) ??
                '/assets/frontend/images/logo/p_logo_21.png'
              "
              alt=""
              class="logo logo_02"
            />
            <img
              :src="
                sanitizeHtml(application_integration_two.right_image_three) ??
                '/assets/frontend/images/logo/p_logo_20.png'
              "
              alt=""
              class="logo logo_03"
            />
            <img
              :src="
                sanitizeHtml(application_integration_two.right_image_four) ??
                '/assets/frontend/images/logo/p_logo_22.png'
              "
              alt=""
              class="logo logo_05"
            />
          </div>
          <!-- /.logo-box -->
        </div>
      </div>
      <!-- /.main-wrapper -->
    </div>
  </div>
</template>
