<script setup>
import { onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();

const checkSuccessPayment = async () => {
  const token = route.params.id;

  const orderIds = token.split(',').map(id => parseInt(id, 10));

  try {
    const response = await fetch("http://localhost/raj-express/backend/controller/onlinePaymentController/paymentSuccess.php", {
      method: "POST",
      headers: {
        "Authorization": token,
      },
    });

    if (!response.ok) {
      const errorMessage = await response.text();
      throw new Error(`HTTP error! status: ${response.status}, message: ${errorMessage}`);
    }

    const result = await response.json();

    if (result && result.success) {
      router.push('/cart');
    } else {
      throw new Error(result.error || "Failed to submit a rating!");
    }
  } catch (error) {
    console.error("Error:", error);
  }
};

onMounted(() => {
  checkSuccessPayment();
});
</script>

<template>
  <div>
    <!-- You can display a loading state or a message here if needed -->
  </div>
</template>
