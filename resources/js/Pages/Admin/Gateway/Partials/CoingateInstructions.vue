<script setup>
import trans from '@/Composables/transComposable'
</script>

<template>
  <div class="mt-5 flex flex-col gap-2 p-1 pr-8">
    <p class="alert-info rounded-md p-3 text-sm">
      <span class="text-nowrap font-bold">{{ trans('Price Currency') }}: </span>
      <a target="_blank" class="underline" href="http://en.wikipedia.org/wiki/ISO_4217">{{
        trans('ISO 4217')
      }}</a>
      {{
        trans(
          'currency code which defines the currency in which you wish to price your merchandise; used to define price parameter.'
        )
      }}
      <a target="_blank" class="underline" href="https://developer.coingate.com/docs/currencies">{{
        trans('Supported currencies')
      }}</a>
    </p>
    <p class="alert-info rounded-md p-3 text-sm">
      <span class="text-nowrap font-bold">{{ trans('Receive Currency') }}: </span>
      <a target="_blank" class="underline" href="http://en.wikipedia.org/wiki/ISO_4217">{{
        trans('ISO 4217')
      }}</a>
      {{
        trans(
          'currency code which defines the currency in which you wish to receive your settlements. Currency conversions are done by CoinGate. Possible values: fiat -'
        )
      }}
      <em>{{ trans('EUR') }}</em
      >;
      {{ trans('stablecoin -') }}
      <em>{{ trans('USDT') }}</em
      >;
      {{ trans('crypto:') }}
      <em>{{ trans('BTC') }}</em
      >, <em>{{ trans('LTC') }}</em
      >,
      <em>{{ trans('ETH') }}</em>
      {{ trans('or') }}
      <em>{{ trans('DO_NOT_CONVERT') }}</em
      >. <strong>{{ trans('Note') }}</strong
      >:
      {{ trans('use') }}
      <em>{{ trans('DO_NOT_CONVERT') }}</em>
      {{
        trans(
          'to keep payments received in original currency (Altcoin payments will be converted to BTC). With'
        )
      }}
      <em>{{ trans('DO_NOT_CONVERT') }}</em>
      {{ trans('you can also extend invoice expiration time up to 24 hours.') }}
      <a target="_blank" class="underline" href="https://developer.coingate.com/docs/currencies">{{
        trans('Supported currencies')
      }}</a>
    </p>
  </div>
</template>
