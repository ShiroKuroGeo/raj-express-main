<template>
  <q-page class="flex flex-center">
    <div class="rating-container q-pa-md">
      <q-header class="bg-red text-white">
        <q-toolbar>
          <q-btn flat round dense icon="arrow_back" @click="$router.back()" />
          <q-toolbar-title>Rating</q-toolbar-title>
        </q-toolbar>
      </q-header>

      <div class="q-mt-lg">
        <q-rating
          v-model="rating"
          size="3em"
          color="yellow"
          icon="star_border"
          icon-selected="star"
          :max="5"
        />
      </div>

      <div class="text-grey q-mt-md">Leave your feedback here</div>

      <div class="q-mt-md">
        <q-btn-toggle
          v-model="selectedFeedback"
          toggle-color="red"
          :options="[
            {label: 'Delicious', value: 'delicious'},
            {label: 'Friendly', value: 'friendly'},
            {label: 'Service', value: 'service'},
            {label: 'Delivery', value: 'delivery'}
          ]"
          spread
          unelevated
          rounded
          multiple
        />
      </div>

      <div class="q-mt-lg">
        <div class="text-h6">Care to share more?</div>
        <q-input
          v-model="feedback"
          type="textarea"
          filled
          placeholder="Share your thoughts about the food to help other buyers. Perhaps, for better taste critic to improve."
        />
      </div>

      <q-btn
        class="full-width q-mt-lg"
        color="red"
        label="Submit"
        @click="submitRating"
      />
    </div>
  </q-page>
</template>

<script>
import { ref } from 'vue'
import { useQuasar } from 'quasar'
import axios from 'axios'

export default {
  setup () {
    const $q = useQuasar()
    const rating = ref(3)
    const selectedFeedback = ref([])
    const feedback = ref('')

    const submitRating = async () => {
      try {
        const response = await axios.post('http://submit_rating.php', {
          rating: rating.value,
          feedback: selectedFeedback.value,
          additionalFeedback: feedback.value
        })
        if (response.data.success) {
          $q.notify({
            color: 'positive',
            message: 'Rating submitted successfully!'
          })
        }
      } catch (error) {
        $q.notify({
          color: 'negative',
          message: 'Failed to submit rating. Please try again.'
        })
      }
    }

    return {
      rating,
      selectedFeedback,
      feedback,
      submitRating
    }
  }
}
</script>

<style lang="scss" scoped>
.rating-container {
  width: 100%;
  max-width: 400px;
}
</style>
