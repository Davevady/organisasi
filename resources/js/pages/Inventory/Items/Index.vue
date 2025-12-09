<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const items = ref([]);
const loading = ref(false);
const filters = ref({
    search: '',
    category_id: null,
    status: 'available'
});

const fetchItems = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/inventory/items', {
            params: filters.value
        });
        items.value = response.data.data;
    } catch (error) {
        console.error('Error fetching items:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchItems();
});
</script>

<template>
    <div>
        <h1>Inventory Items</h1>

        <!-- Search & Filters -->
        <div class="filters">
            <input v-model="filters.search" @input="fetchItems" placeholder="Search..." />
            <select v-model="filters.status" @change="fetchItems">
                <option value="">All Status</option>
                <option value="available">Available</option>
                <option value="unavailable">Unavailable</option>
            </select>
        </div>

        <!-- Items Table -->
        <table v-if="!loading">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in items" :key="item.id">
                    <td>{{ item.code }}</td>
                    <td>{{ item.name }}</td>
                    <td>{{ item.category.name }}</td>
                    <td>{{ item.current_stock }} {{ item.unit.symbol }}</td>
                    <td>
                        <span :class="['badge', item.status]">
                            {{ item.status }}
                        </span>
                    </td>
                    <td>
                        <a :href="`/inventory/items/${item.id}`">View</a>
                        <a :href="`/inventory/items/${item.id}/edit`">Edit</a>
                    </td>
                </tr>
            </tbody>
        </table>

        <div v-else>Loading...</div>
    </div>
</template>