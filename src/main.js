import { createApp } from 'vue';
import { Quasar, Notify, Dialog } from 'quasar';
import quasarUserOptions from './quasar-user-options';
import 'quasar/src/css/index.sass';
import App from './App.vue';
import './assets/print.css';
import router from './router';

import store from './stores'; // Import the Vuex store
import { Quasar } from 'quasar';
import axios from 'axios';


axios.defaults.baseURL = 'http://localhost/raj-express/backend/controller';
axios.defaults.withCredentials = true;

const app = createApp(App);

app.use(store);
app.use(router);
app.use(Quasar, quasarUserOptions);
app.use(Notify);
app.use(Dialog);

app.mount('#app');
