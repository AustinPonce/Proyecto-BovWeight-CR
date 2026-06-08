import { createRouter, createWebHistory } from "vue-router";

import Login from "../views/auth/Login.vue";
import CrearCuenta from "../views/auth/CrearCuenta.vue";

import Dashboard from "../views/dashboard/Dashboard.vue";

import MisFincas from "../views/fincas/MisFincas.vue";

import MisBovinos from "../views/bovinos/MisBovinos.vue";

import Paso1General from "../views/bovinos/RegistrarVaca/Paso1General.vue";
import Paso2Medidas from "../views/bovinos/RegistrarVaca/Paso2Medidas.vue";

import Notificaciones from "../views/notificaciones/Notificaciones.vue";

import Perfil from "../views/perfil/Perfil.vue";
import Configuracion from "../views/perfil/Configuracion.vue";

import Reportes from "../views/reportes/Reportes.vue";

const routes = [
    {
        path: '/',
        redirect: '/login'
    },

    {
        path: '/login',
        component: Login
    },

    {
        path: '/registro',
        component: CrearCuenta
    },

    {
        path: '/dashboard',
        component: Dashboard
    },

    {
        path: '/fincas',
        component: MisFincas
    },

    {
        path: '/bovinos',
        component: MisBovinos
    },

    {
        path: '/bovinos/registrar/paso1',
        component: Paso1General
    },

    {
        path: '/bovinos/registrar/paso2',
        component: Paso2Medidas
    },

    {
        path: '/notificaciones',
        component: Notificaciones
    },

    {
        path: '/perfil',
        component: Perfil
    },

    {
        path: '/configuracion',
        component: Configuracion
    },

    {
        path: '/reportes',
        component: Reportes
    }
];

export default createRouter({
    history: createWebHistory(),
    routes
});