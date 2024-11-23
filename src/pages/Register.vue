<template>
  <q-layout>
    <q-page-container>
      <q-page class="flex flex-center">
        <div class="q-pa-md q-ma-md">
          <div>
            <q-form class="q-gutter-md" @submit.prevent="signup">
              <h3>Sign Up</h3>

              <q-input v-model="first_name" label="First Name" dense outlined />
              <q-input v-model="last_name" label="Last Name" dense outlined />
              <q-input v-model="address" label="Address" dense outlined />
              <q-input v-model="contact_number" label="Phone Number" dense outlined />
              <q-input v-model="email" label="Email" dense outlined />

              <q-input
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                label="Password"
                dense
                outlined
              >
                <template v-slot:append>
                  <q-icon
                    :name="showPassword ? 'visibility_off' : 'visibility'"
                    @click="togglePasswordVisibility"
                    class="cursor-pointer"
                  />
                </template>
              </q-input>

              <q-input
                v-model="confirmpassword"
                :type="showPassword ? 'text' : 'password'"
                label="Confirm Password"
                dense
                outlined
              >
                <template v-slot:append>
                  <q-icon
                    :name="showPassword ? 'visibility_off' : 'visibility'"
                    @click="togglePasswordVisibility"
                    class="cursor-pointer"
                  />
                </template>
              </q-input>

              <q-btn type="submit" label="Sign Up" color="primary" class="q-mt-md" />
              <p class="q-mt-md">Already Have an Account? <q-btn flat @click="goToSignIn">Click Here</q-btn></p>
            </q-form>
          </div>
        </div>
      </q-page>
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useQuasar } from 'quasar';

const $q = useQuasar();
const first_name = ref("");
const last_name = ref("");
const address = ref("");
const contact_number = ref("");
const email = ref("");
const password = ref("");
const confirmpassword = ref("");
const showPassword = ref(false);
const router = useRouter();

const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value;
};

const showNotification = (message, color, icon, timeout = 5000) => {
  $q.notify({
    message,
    color,
    icon,
    position: 'top',
    timeout,
    actions: [
      { label: 'Dismiss', color: 'white', handler: () => { /* ... */ } }
    ]
  });
};

const signup = async () => {
  if (password.value !== confirmpassword.value) {
    showNotification('Passwords do not match!', 'red', 'error', 3000);
    return;
  }

  const userData = {
    first_name: first_name.value,
    last_name: last_name.value,
    address: address.value,
    contact_number: contact_number.value,
    email: email.value,
    password: password.value,
    user_type: 'Customer',
    status: 'Active'
  };

  try {
    const response = await fetch("http://localhost/raj-express/backend/auth/register.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(userData)
    });

    const result = await response.json();

    if (response.ok) {
      showNotification('Registration successful!', 'green', 'check_circle', 3000);
      // Redirect to login page after a short delay
      setTimeout(() => {
        router.push("/"); // Assuming "/" is your login route
      }, 1500); // Delay for 1.5 seconds so the user can see the success message
    } else {
      showNotification(result.error || 'Registration failed', 'red', 'error', 3000);
    }
  } catch (error) {
    console.error(error);
    showNotification(`An error occurred: ${error.message}`, 'red', 'error', 3000);
  }
};

const goToSignIn = () => {
  router.push("/");
};
</script>

<style scoped>
/* You can add custom styles for your notifications here if needed */
</style>

