<script setup lang="ts">
  import { useForm, usePage } from '@inertiajs/vue3'
  import CPlaceCard from './CPlaceCard.vue'
  import CTitle from './CTitle.vue'

  const page = usePage<{
    flash: { success?: string }
  }>()

  const services = [
    'Reparación de ordenadores',
    'Reparación de tablets',
    'Reparación de móviles',
    'Reparación de impresoras',
    'Mantenimiento IT empresa',
    'Servicio a domicilio',
    'Electrónica en placa',
    'Página web',
    'Dashboard empresarial',
    'Otros'
  ]

  const contactForm = useForm({
    name: '',
    email: '',
    phone: '',
    service: '',
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
  <div class="c-contact">
    <div class="c-contact__inner">
      <CTitle
        :overline="'Contacto'"
        :title="'¿Hablamos de tu proyecto?'"
        :description="'Cuéntanos qué necesitas y te enviaremos un presupuesto sin compromiso en menos de 24 horas.'"
        :color="'secondary'"
      />
      <div class="c-contact__container o-container">
        <div class="c-contact__col c-contact__col--cards o-col-4@md o-col-4@sm o-col-4@xs">
          <CPlaceCard
            :icon="'IconPhone'"
            :title="'+34 678 758 210'"
            :description="'Contáctanos por teléfono o WhatsApp'"
            :iconColor="'primary'"
            :iconSize="'24px'"
            :is-wrapper="true"
          />
          <CPlaceCard
            :icon="'IconEmail'"
            :title="'info@icorebyte.com'"
            :description="'Respuesta en menos de 24 horas'"
            :iconColor="'primary'"
            :iconSize="'24px'"
            :is-wrapper="true"
          />
          <CPlaceCard
            :icon="'IconLocation'"
            :title="'Ubicación'"
            :description="'Carretera de Palamós 57 local, Sant Feliu de Guixols, Girona, 17220'"
            :iconColor="'primary'"
            :iconSize="'24px'"
            :is-wrapper="true"
          />
          <CPlaceCard
            :icon="'IconTime'"
            :title="'Horario'"
            :description="'Lunes a Viernes: 10:00 - 13:30 y 17:00 - 20:00'"
            :iconColor="'primary'"
            :iconSize="'24px'"
            :is-wrapper="true"
          />
        </div>

        <div class="c-contact__col o-col-8@md o-col-4@sm o-col-4@xs">
          <div class="c-contact__form-wrapper">
            <!-- Flash success -->
            <div v-if="page.props.flash.success" class="c-contact__flash">
              {{ page.props.flash.success }}
            </div>

            <form @submit.prevent="sendContact" novalidate class="c-contact__form">
              <!-- Fila 1: Nombre + Email -->
              <div class="c-contact__row">
                <div class="c-contact__field">
                  <label class="c-contact__label">Nombre</label>
                  <input
                    v-model="contactForm.name"
                    type="text"
                    placeholder="Tu nombre"
                    class="c-contact__input"
                  />
                  <p v-if="contactForm.errors.name" class="c-contact__error">
                    {{ contactForm.errors.name }}
                  </p>
                </div>
                <div class="c-contact__field">
                  <label class="c-contact__label">Email</label>
                  <input
                    v-model="contactForm.email"
                    type="email"
                    placeholder="tu@email.com"
                    class="c-contact__input"
                  />
                  <p v-if="contactForm.errors.email" class="c-contact__error">
                    {{ contactForm.errors.email }}
                  </p>
                </div>
              </div>

              <!-- Fila 2: Teléfono + Servicio -->
              <div class="c-contact__row">
                <div class="c-contact__field">
                  <label class="c-contact__label">Teléfono</label>
                  <input
                    v-model="contactForm.phone"
                    type="tel"
                    placeholder="+34 XXX XXX XXX"
                    class="c-contact__input"
                  />
                  <p v-if="contactForm.errors.phone" class="c-contact__error">
                    {{ contactForm.errors.phone }}
                  </p>
                </div>
                <div class="c-contact__field">
                  <label class="c-contact__label">Servicio</label>
                  <div class="c-contact__select-wrapper">
                    <select
                      v-model="contactForm.service"
                      class="c-contact__input c-contact__input--select"
                      :class="{ 'c-contact__input--placeholder': !contactForm.service }"
                    >
                      <option value="" disabled>Selecciona un servicio</option>
                      <option v-for="service in services" :key="service" :value="service">
                        {{ service }}
                      </option>
                    </select>
                    <svg
                      class="c-contact__select-arrow"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M19 9l-7 7-7-7"
                      />
                    </svg>
                  </div>
                  <p v-if="contactForm.errors.service" class="c-contact__error">
                    {{ contactForm.errors.service }}
                  </p>
                </div>
              </div>

              <!-- Mensaje -->
              <div class="c-contact__field">
                <label class="c-contact__label">Mensaje</label>
                <textarea
                  v-model="contactForm.message"
                  placeholder="Describe tu proyecto o necesidad..."
                  rows="6"
                  class="c-contact__input c-contact__input--textarea"
                ></textarea>
                <p v-if="contactForm.errors.message" class="c-contact__error">
                  {{ contactForm.errors.message }}
                </p>
              </div>

              <!-- Botón -->
              <button type="submit" :disabled="contactForm.processing" class="c-contact__submit">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"
                  />
                </svg>
                {{ contactForm.processing ? 'Enviando...' : 'Enviar Mensaje' }}
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
  .c-contact {
    &__inner {
      padding: 4rem 0;
    }

    &__container {
      display: flex;
      gap: 2rem;
    }

    &__col {
      &--cards {
        display: flex;
        flex-direction: column;
        justify-content: space-evenly;
      }
    }

    &__form-wrapper {
      background: var(--color-primary-dark);
      border: 1px solid var(--color-gray-dark);
      border-radius: 1rem;
      padding: 2rem;
    }

    &__flash {
      margin-bottom: 1.5rem;
      background: rgba(0, 107, 223, 0.1);
      border: 1px solid rgba(0, 107, 223, 0.3);
      color: var(--color-primary);
      padding: 0.75rem 1rem;
      border-radius: 0.75rem;
      font-size: 0.875rem;
    }

    &__form {
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    &__row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;

      @media (max-width: 640px) {
        grid-template-columns: 1fr;
      }
    }

    &__field {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }

    &__label {
      font-size: 0.75rem;
      font-weight: 600;
      color: rgba(255, 255, 255, 0.6);
      text-transform: uppercase;
      letter-spacing: 0.1em;
    }

    &__input {
      width: 100%;
      background: var(--color-black);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 0.75rem;
      padding: 0.75rem 1rem;
      color: var(--color-white);
      font-size: 1rem;
      transition: border-color 0.2s;

      &::placeholder {
        color: rgba(255, 255, 255, 0.3);
      }

      &:focus {
        outline: none;
        border-color: var(--color-primary);
      }

      &--textarea {
        resize: none;
      }

      &--select {
        appearance: none;
        cursor: pointer;
      }

      &--placeholder {
        color: rgba(255, 255, 255, 0.3);
      }

      option {
        background: var(--color-black);
        color: var(--color-white);
      }
    }

    &__select-wrapper {
      position: relative;
    }

    &__select-arrow {
      position: absolute;
      right: 1rem;
      top: 50%;
      transform: translateY(-50%);
      width: 1rem;
      height: 1rem;
      color: rgba(255, 255, 255, 0.4);
      pointer-events: none;
    }

    &__error {
      color: #f87171;
      font-size: 0.75rem;
      margin: 0;
    }

    &__submit {
      width: 100%;
      padding: 1rem;
      border-radius: 0.75rem;
      font-weight: 600;
      color: var(--color-white);
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      background: var(--color-gradient);
      transition: opacity 0.2s;
      margin-top: 0.5rem;

      svg {
        width: 1rem;
        height: 1rem;
      }

      &:disabled {
        opacity: 0.5;
        cursor: not-allowed;
      }

      &:hover:not(:disabled) {
        opacity: 0.9;
      }
    }
  }
</style>
