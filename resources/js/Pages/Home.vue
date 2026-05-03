<script setup lang="ts">
  import CContact from '@/Components/CContact.vue'
  import CDevelopment from '@/Components/CDevelopment.vue'
  import CHero from '@/Components/CHero.vue'
  import CServiceList from '@/Components/CServiceList.vue'
  import CShop from '@/Components/CShop.vue'

  import { useForm, usePage } from '@inertiajs/vue3'

  const page = usePage<{
    flash: { success?: string }
  }>()

  const contactForm = useForm({
    name: '',
    email: '',
    message: ''
  })

  const sendContact = () => {
    contactForm.post(route('contact.send'), {
      preserveScroll: true,
      onSuccess: () => contactForm.reset()
    })
  }
</script>

<template>
  <PublicLayout>
    <CHero />
    <CServiceList />
    <CShop />
    <CDevelopment />
    <CContact />
  </PublicLayout>
</template>
