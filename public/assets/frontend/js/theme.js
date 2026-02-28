// js Document

;(function ($) {
  'use strict'


  //-------------- Click event to scroll to top
  $(window).on('scroll', function () {
    if ($(this).scrollTop() > 200) {
      $('.scroll-top').fadeIn()
    } else {
      $('.scroll-top').fadeOut()
    }
  })
  $('.scroll-top').on('click', function () {
    $('html, body').animate({ scrollTop: 0 })
    return false
  })

  // --------------------- Add .active class to current navigation based on URL
  var pgurl = window.location.href.substr(window.location.href.lastIndexOf('/') + 1)
  $('.navbar-nav > li  a').each(function () {
    if ($(this).attr('href') == pgurl || $(this).attr('href') == '') $(this).addClass('active')
    
  })

  // ------------------------ Product Quantity Selector
  if ($('.product-value').length) {
    $('.value-increase').on('click', function () {
      var $qty = $(this).closest('ul').find('.product-value')
      var currentVal = parseInt($qty.val())
      if (!isNaN(currentVal)) {
        $qty.val(currentVal + 1)
      }
    })
    $('.value-decrease').on('click', function () {
      var $qty = $(this).closest('ul').find('.product-value')
      var currentVal = parseInt($qty.val())
      if (!isNaN(currentVal) && currentVal > 1) {
        $qty.val(currentVal - 1)
      }
    })
  }

  // ------------------------ Credit Card Option
  if ($('#credit-card').length) {
    $('.payment-radio-button').on('click', function () {
      if ($('#credit-card').is(':checked')) {
        $('.credit-card-form').show(300)
      } else {
        $('.credit-card-form').hide(300)
      }
    })
  }

  // ----------------------------- Counter Function
  var timer = $('.counter')
  if (timer.length) {
    $('.counter').counterUp({
      delay: 10,
      time: 1200
    })
  }

  // ------------------------ Navigation Scroll
  $(window).on('scroll', function () {
    var sticky = $('.sticky-menu'),
      scroll = $(window).scrollTop()
    if (scroll >= 180) sticky.addClass('fixed')
    else sticky.removeClass('fixed')
  })

  // -------------------- Remove Placeholder When Focus Or Click
  $('input,textarea').each(function () {
    $(this).data('holder', $(this).attr('placeholder'))
    $(this).on('focusin', function () {
      $(this).attr('placeholder', '')
    })
    $(this).on('focusout', function () {
      $(this).attr('placeholder', $(this).data('holder'))
    })
  })

  // ------------------------ Partner Slider One
  if ($('.partner-logo-one').length) {
    $('.partner-logo-one').slick({
      dots: false,
      arrows: false,
      lazyLoad: 'ondemand',
      centerPadding: '0px',
      slidesToShow: 6,
      slidesToScroll: 1,
      autoplay: true,
      autoplaySpeed: 3000,
      responsive: [
        {
          breakpoint: 1400,
          settings: {
            slidesToShow: 6
          }
        },
        {
          breakpoint: 992,
          settings: {
            slidesToShow: 5
          }
        },
        {
          breakpoint: 767,
          settings: {
            slidesToShow: 4
          }
        },
        {
          breakpoint: 576,
          settings: {
            slidesToShow: 2
          }
        }
      ]
    })
  }

  // ------------------------ Partner Slider Two
  if ($('.partner-logo-two').length) {
    $('.partner-logo-two').slick({
      dots: false,
      arrows: false,
      lazyLoad: 'ondemand',
      centerPadding: '0px',
      slidesToShow: 8,
      slidesToScroll: 1,
      autoplay: true,
      autoplaySpeed: 3000,
      responsive: [
        {
          breakpoint: 1400,
          settings: {
            slidesToShow: 6
          }
        },
        {
          breakpoint: 992,
          settings: {
            slidesToShow: 5
          }
        },
        {
          breakpoint: 767,
          settings: {
            slidesToShow: 4
          }
        },
        {
          breakpoint: 576,
          settings: {
            slidesToShow: 3
          }
        }
      ]
    })
  }

  // ------------------------ VS Slider One
  if ($('.vs-slide-one').length) {
    $('.vs-slide-one').slick({
      dots: false,
      arrows: false,
      lazyLoad: 'ondemand',
      centerPadding: '0px',
      slidesToShow: 5,
      slidesToScroll: 1,
      variableWidth: true,
      autoplay: true,
      autoplaySpeed: 3000,
      responsive: [
        {
          breakpoint: 1400,
          settings: {
            slidesToShow: 4
          }
        },
        {
          breakpoint: 992,
          settings: {
            slidesToShow: 3
          }
        },
        {
          breakpoint: 767,
          settings: {
            slidesToShow: 2
          }
        },
        {
          breakpoint: 576,
          settings: {
            slidesToShow: 1
          }
        }
      ]
    })
  }

  // ------------------------ VS Slider Two
  if ($('.vs-slide-two').length) {
    $('.vs-slide-two').slick({
      dots: false,
      arrows: false,
      lazyLoad: 'ondemand',
      centerPadding: '0px',
      slidesToShow: 5,
      slidesToScroll: 1,
      variableWidth: true,
      autoplay: true,
      rtl: true,
      autoplaySpeed: 3000,
      responsive: [
        {
          breakpoint: 1400,
          settings: {
            slidesToShow: 4
          }
        },
        {
          breakpoint: 992,
          settings: {
            slidesToShow: 3
          }
        },
        {
          breakpoint: 767,
          settings: {
            slidesToShow: 2
          }
        },
        {
          breakpoint: 576,
          settings: {
            slidesToShow: 1
          }
        }
      ]
    })
  }

  // ------------------------ Feedback Slider One
  if ($('.feedback-slide-one').length) {
    $('.feedback-slide-one').slick({
      dots: true,
      arrows: false,
      lazyLoad: 'ondemand',
      centerPadding: '0px',
      slidesToShow: 2,
      slidesToScroll: 1,
      autoplay: true,
      autoplaySpeed: 3000,
      responsive: [
        {
          breakpoint: 992,
          settings: {
            slidesToShow: 1
          }
        }
      ]
    })
  }

  // ------------------------ Feedback Slider Two
  if ($('.feedback-slider-two').length) {
    $('.feedback-slider-two').slick({
      dots: true,
      arrows: false,
      lazyLoad: 'ondemand',
      centerPadding: '0px',
      slidesToShow: 3,
      slidesToScroll: 1,
      centerMode: true,
      autoplay: false,
      autoplaySpeed: 3000,
      responsive: [
        {
          breakpoint: 992,
          settings: {
            slidesToShow: 1
          }
        }
      ]
    })
  }

  // ------------------------ Feedback Slider Three
  if ($('.feedback-slider-three').length) {
    $('.feedback-slider-three').slick({
      dots: false,
      arrows: true,
      prevArrow: $('.prev_b'),
      nextArrow: $('.next_b'),
      lazyLoad: 'ondemand',
      centerPadding: '0px',
      slidesToShow: 3,
      slidesToScroll: 1,
      centerMode: true,
      autoplay: true,
      autoplaySpeed: 3000,
      responsive: [
        {
          breakpoint: 1200,
          settings: {
            slidesToShow: 2
          }
        },
        {
          breakpoint: 768,
          settings: {
            slidesToShow: 1
          }
        }
      ]
    })
  }

  // ------------------------ Feedback Slider Four
  if ($('.feedback-slider-four').length) {
    $('.feedback-slider-four').slick({
      dots: false,
      arrows: true,
      prevArrow: $('.prev_d'),
      nextArrow: $('.next_d'),
      lazyLoad: 'ondemand',
      centerPadding: '0px',
      slidesToShow: 4,
      slidesToScroll: 1,
      centerMode: true,
      autoplay: true,
      autoplaySpeed: 3000,
      responsive: [
        {
          breakpoint: 1400,
          settings: {
            slidesToShow: 3
          }
        },
        {
          breakpoint: 992,
          settings: {
            slidesToShow: 2
          }
        },
        {
          breakpoint: 768,
          settings: {
            slidesToShow: 1
          }
        }
      ]
    })
  }

  // ------------------------ Feedback Slider Five
  if ($('.feedback-slide-five').length) {
    $('.feedback-slide-five').slick({
      dots: false,
      arrows: false,
      lazyLoad: 'ondemand',
      centerPadding: '0px',
      slidesToShow: 3,
      slidesToScroll: 1,
      autoplay: true,
      autoplaySpeed: 3000,
      responsive: [
        {
          breakpoint: 992,
          settings: {
            slidesToShow: 1
          }
        }
      ]
    })
  }

  // ------------------------ Template Slider
  if ($('.tm-slider-one').length) {
    $('.tm-slider-one').slick({
      dots: false,
      arrows: false,
      lazyLoad: 'ondemand',
      centerPadding: '0px',
      slidesToShow: 3,
      slidesToScroll: 1,
      centerMode: true,
      autoplay: true,
      autoplaySpeed: 0,
      speed: 20000,
      cssEase: 'linear',
      pauseOnFocus: false,
      pauseOnHover: false,
      vertical: true,
      verticalScrolling: true,
      responsive: [
        {
          breakpoint: 1400,
          settings: {
            slidesToShow: 3
          }
        }
      ]
    })
  }

  // ------------------------ AI Tools Slider
  if ($('.ai-tools-slider').length) {
    $('.ai-tools-slider').slick({
      dots: false,
      arrows: false,
      lazyLoad: 'ondemand',
      centerPadding: '0px',
      slidesToShow: 3,
      slidesToScroll: 1,
      centerMode: true,
      autoplay: true,
      autoplaySpeed: 3000,
      pauseOnHover: true,
      responsive: [
        {
          breakpoint: 768,
          settings: {
            slidesToShow: 1
          }
        }
      ]
    })
  }

  // ------------------------ Instagram Feed Slider
  if ($('.insta-slider').length) {
    $('.insta-slider').slick({
      dots: false,
      arrows: false,
      lazyLoad: 'ondemand',
      centerPadding: '0px',
      slidesToShow: 5,
      slidesToScroll: 1,
      centerMode: true,
      autoplay: true,
      autoplaySpeed: 3000,
      pauseOnHover: true,
      responsive: [
        {
          breakpoint: 1200,
          settings: {
            slidesToShow: 4
          }
        },
        {
          breakpoint: 768,
          settings: {
            slidesToShow: 3
          }
        },
        {
          breakpoint: 576,
          settings: {
            slidesToShow: 2
          }
        }
      ]
    })
  }

  // ------------------------ Service Slider One
  if ($('.service-slider-one').length) {
    $('.service-slider-one').slick({
      dots: false,
      arrows: true,
      prevArrow: $('.prev_c'),
      nextArrow: $('.next_c'),
      lazyLoad: 'ondemand',
      centerPadding: '0px',
      slidesToShow: 4,
      slidesToScroll: 1,
      centerMode: true,
      autoplay: false,
      autoplaySpeed: 3000,
      pauseOnHover: true,
      responsive: [
        {
          breakpoint: 992,
          settings: {
            slidesToShow: 3
          }
        },
        {
          breakpoint: 768,
          settings: {
            slidesToShow: 2
          }
        }
      ]
    })
  }

  // ------------------------ Project Slider One
  if ($('.project-slider-one').length) {
    $('.project-slider-one').slick({
      dots: false,
      arrows: true,
      prevArrow: $('.prev_a'),
      nextArrow: $('.next_a'),
      lazyLoad: 'ondemand',
      centerPadding: '0px',
      slidesToShow: 3,
      slidesToScroll: 1,
      centerMode: true,
      autoplay: false,
      autoplaySpeed: 3000,
      responsive: [
        {
          breakpoint: 576,
          settings: {
            slidesToShow: 1
          }
        }
      ]
    })
  }


  // ------------------------ Password Toggler
  if ($('.user-data-form').length) {
    $('.passVicon').on('click', function () {
      $('.passVicon').toggleClass('eye-slash')
      var input = $('.pass_log_id')
      if (input.attr('type') === 'password') {
        input.attr('type', 'text')
      } else {
        input.attr('type', 'password')
      }
    })
  }



  $(window).on('load', function () {
    // makes sure the whole site is loaded

    // -------------------- Site Preloader
    $('#ctn-preloader').fadeOut() // will first fade out the loading animation
    $('#preloader').delay(350).fadeOut('slow') // will fade out the white DIV that covers the website.
    $('body').delay(350).css({ overflow: 'visible' })

    // ------------------------------------- Fancybox
    var fancy = $('[data-fancybox]')
    if (fancy.length) {
      Fancybox.bind('[data-fancybox]', {
        // Your custom options
      })
    }

    // ----------------------------- isotop gallery
    if ($('#isotop-gallery-wrapper').length) {
      var $grid = $('#isotop-gallery-wrapper').isotope({
        // options
        itemSelector: '.isotop-item',
        percentPosition: true,
        masonry: {
          // use element for option
          columnWidth: '.grid-sizer'
        }
      })

      // filter items on button click
      $('.isotop-menu-wrapper').on('click', 'li', function () {
        var filterValue = $(this).attr('data-filter')
        $grid.isotope({ filter: filterValue })
      })

      // change is-checked class on buttons
      $('.isotop-menu-wrapper').each(function (i, buttonGroup) {
        var $buttonGroup = $(buttonGroup)
        $buttonGroup.on('click', 'li', function () {
          $buttonGroup.find('.is-checked').removeClass('is-checked')
          $(this).addClass('is-checked')
        })
      })
    }

    // partical JS Bg
    if ($('#particles-js-three').length) {
      particlesJS('particles-js-three', {
        particles: {
          number: {
            value: 160,
            density: {
              enable: true,
              value_area: 800
            }
          },
          color: {
            value: '#ffffff'
          },
          shape: {
            type: 'circle',
            stroke: {
              width: 0,
              color: '#000000'
            },
            polygon: {
              nb_sides: 5
            },
            image: {
              src: 'img/github.svg',
              width: 100,
              height: 100
            }
          },
          opacity: {
            value: 1,
            random: true,
            anim: {
              enable: true,
              speed: 1,
              opacity_min: 0,
              sync: false
            }
          },
          size: {
            value: 3,
            random: true,
            anim: {
              enable: false,
              speed: 4,
              size_min: 0.3,
              sync: false
            }
          },
          line_linked: {
            enable: false,
            distance: 150,
            color: '#ffffff',
            opacity: 0.4,
            width: 1
          },
          move: {
            enable: true,
            speed: 1,
            direction: 'none',
            random: true,
            straight: false,
            out_mode: 'out',
            bounce: false,
            attract: {
              enable: false,
              rotateX: 600,
              rotateY: 600
            }
          }
        },
        interactivity: {
          detect_on: 'canvas',
          events: {
            onhover: {
              enable: true,
              mode: 'bubble'
            },
            onclick: {
              enable: true,
              mode: 'repulse'
            },
            resize: true
          },
          modes: {
            grab: {
              distance: 400,
              line_linked: {
                opacity: 1
              }
            },
            bubble: {
              distance: 250,
              size: 0,
              duration: 2,
              opacity: 0,
              speed: 3
            },
            repulse: {
              distance: 400,
              duration: 0.4
            },
            push: {
              particles_nb: 4
            },
            remove: {
              particles_nb: 2
            }
          }
        },
        retina_detect: true
      })
    }
  }) //End On Load Function
})(jQuery)
