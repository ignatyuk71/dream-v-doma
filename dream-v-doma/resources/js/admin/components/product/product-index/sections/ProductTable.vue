<template>
  <div class="filters mb-4 card" style="border-radius:5px;">
    <table class="table align-middle">
      <thead>
        <tr>
          <th><input type="checkbox" /></th>
          <th>Product</th>
          <th>Category</th>
          <th>Stock</th>
          <th>SKU</th>
          <th>Price</th>
          <th>Qty</th>
          <th>Status</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(product, idx) in products" :key="product.id">
          <td><input type="checkbox" /></td>
          <td>
            <div class="d-flex align-items-center">
              <img :src="product.image" class="avatar" />
              <div>
                <div class="product-name">{{ product.name }}</div>
                <div class="product-sub">{{ product.brand }}</div>
              </div>
            </div>
          </td>
          <td>
            <span class="d-flex align-items-center">
              <i class="bi bi-headphones me-2 text-danger"></i>{{ product.category_name }}
            </span>
          </td>
          <td>
            <div
              :class="['toggle-switch', { active: product.status == 1 }]"
              @click="toggleStatus(product)"
              style="cursor: pointer;"
              title="Змінити статус"
            ></div>
          </td>
          <td>{{ product.sku }}</td>
          <td>{{ product.price }}</td>
          <td>{{ product.qty }}</td>
          <td>
            <span :class="['status-badge', statusClass(product.status)]">
              {{ statusLabel(product.status) }}
            </span>
          </td>
          <td class="actions-cell">
            <a
              :href="`/admin/products/${product.id}/edit`"
              class="edit-btn text-dark"
              title="Редагувати"
            >
              <i class="bi bi-pencil-square"></i>
            </a>
            <button
              class="dots-menu"
              @click="openMenu(idx)"
              type="button"
              title="Ще дії"
            >
              <i class="bi bi-three-dots-vertical"></i>
            </button>
            <div
              v-if="menuOpen === idx"
              class="dropdown-menu show"
              @mouseleave="closeMenu"
            >
              <a class="dropdown-item" @click.prevent="download(product)">
                <i class="bi bi-download me-2"></i> Download
              </a>
              <a
                class="dropdown-item text-danger"
                @click.prevent="deleteProduct(product)"
              >
                <i class="bi bi-trash me-2"></i> Delete
              </a>
              <a class="dropdown-item" @click.prevent="duplicate(product)">
                <i class="bi bi-files me-2"></i> Duplicate
              </a>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>


<script>
import axios from 'axios'

export default {
  name: 'ProductTable',
  props: {
    products: { type: Array, required: true }
  },
  data() {
    return {
      menuOpen: null
    }
  },
  methods: {
    statusLabel(status) {
      if (status == 1) return 'Опубліковано'
      if (status == 2) return 'Заплановано'
      if (status == 0) return 'Неактивний'
      return ''
    },
    statusClass(status) {
      if (status == 1) return 'status-publish'
      if (status == 2) return 'status-scheduled'
      if (status == 0) return 'status-inactive'
      return ''
    },
    async toggleStatus(product) {
      const oldStatus = product.status
      product.status = oldStatus == 1 ? 0 : 1

      try {
        await axios.post(`/api/products/${product.id}/toggle-status`, { status: product.status })
      } catch (error) {
        product.status = oldStatus
        alert('Помилка збереження статусу')
      }
    },
    openMenu(idx) {
      if (this.menuOpen === idx) {
        this.closeMenu()
      } else {
        this.menuOpen = idx
        document.addEventListener('click', this.handleOutsideClick)
      }
    },
    closeMenu() {
      this.menuOpen = null
      document.removeEventListener('click', this.handleOutsideClick)
    },
    handleOutsideClick(e) {
      if (!e.target.closest('.dropdown-menu') && !e.target.closest('.dots-menu')) {
        this.closeMenu()
      }
    },
    async deleteProduct(product) {
    this.closeMenu()

    if (!confirm('Підтвердіть видалення товару. Операція незворотна і не може бути скасована.')) {
      return
    }

    try {
      await axios.delete(`/api/products/${product.id}`)
      alert('Товар успішно видалено')
      window.location.reload()
    } catch (error) {
      alert('Помилка видалення товару')
    }
  },
    download(product) {
      this.closeMenu()
      alert(`Download product #${product.id}`)
    },
    duplicate(product) {
      this.closeMenu()
      this.$emit('duplicate', product)
    }
  }
}
</script>





  
<style scoped>/* Таблиця */
.dropdown-menu.show {
  position: fixed !important;
  top: auto !important;
  bottom: auto !important;

  z-index: 2000 !important;
  max-height: none !important;
  overflow: visible !important;
  transform: translate(calc(100% - 180px), 90px) !important; /* зміщення вправо і вниз */
}
  .toggle-switch {
  width: 38px;
  height: 20px;
  background-color: #e5e7eb;
  border-radius: 999px;
  position: relative;
  transition: all 0.3s ease;
}
.toggle-switch::before {
  content: '';
  width: 16px;
  height: 16px;
  background-color: white;
  border-radius: 50%;
  position: absolute;
  top: 2px;
  left: 2px;
  transition: all 0.3s ease;
}
.toggle-switch.active {
  background-color: #22c55e;
}
.toggle-switch.active::before {
  transform: translateX(18px);
}

  .table {
    width: 100%;
    border-collapse: collapse; /* Щоб лінії були суцільні */
    background-color: #fff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(17, 38, 146, 0.05);
  }
  
  .table th,
  .table td {
    padding: 12px 15px;
    border-bottom: 1px solid #e4e6f1;
    vertical-align: middle;
    font-size: 14px;
    color: #374151;
  }
  
  .table th {
    color: #6b7280;
    font-weight: 600;
    text-transform: uppercase;
    background: #f9fafb;
  }
  
  .table tbody tr:last-child td {
    border-bottom: none; /* Прибрати лінію у останнього рядка */
  }
  
  /* Аватар */
  .avatar {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    object-fit: cover;
    margin-right: 12px;
  }
  
  /* Ім'я товару */
  .product-name {
    font-weight: 600;
    color: #111827;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 300px;
  }
  
  /* Підназва */
  .product-sub {
    font-size: 13px;
    color: #6b7280;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 300px;
  }
  
  /* Статус */
  .status-badge {
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    display: inline-block;
    white-space: nowrap;
  }
  
  .status-publish {
    background-color: #e9fdf0;
    color: #22c55e;
  }
  
  .status-scheduled {
    background-color: #fff7ed;
    color: #f59e0b;
  }
  
  .status-inactive {
    background-color: #ffecec;
    color: #ef4444;
  }
  
  /* Перемикач статусу */
  .toggle-switch {
    width: 38px;
    height: 20px;
    background-color: #e5e7eb;
    border-radius: 999px;
    position: relative;
    cursor: pointer;
    transition: all 0.3s ease;
    flex-shrink: 0;
  }
  
  .toggle-switch::before {
    content: '';
    width: 16px;
    height: 16px;
    background-color: white;
    border-radius: 50%;
    position: absolute;
    top: 2px;
    left: 2px;
    transition: all 0.3s ease;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  }
  
  .toggle-switch.active {
    background-color: #6366f1;
  }
  
  .toggle-switch.active::before {
    transform: translateX(18px);
  }
  
  /* Колонки дій */
  .actions-cell {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    position: relative;
    min-width: 140px;
  }
  
  /* Кнопка редагування */
  .edit-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    border: none;
    font-size: 20px;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    transition: background 0.15s;
    color: #374151;
  }
  
  .edit-btn:hover {
    background: #f2f2f5;
    color: #1f2937;
  }
  
  /* Кнопка меню */
  .dots-menu {
    border: none;
    background: #f2f2f5;
    border-radius: 50%;
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    padding: 0;
    cursor: pointer;
    transition: background 0.2s;
    color: #374151;
    box-shadow: none !important;
  }
  
  .dots-menu:hover,
  .dots-menu:focus {
    background: #ebebfa;
    color: #6366f1;
    outline: none;
  }
  
  /* Випадаюче меню */
  .dropdown-menu.show {
    display: block;
    min-width: 180px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 16px rgba(40, 50, 130, 0.12);
    padding: 0.5rem 0;
    margin-top: 6px;
    position: absolute;
    right: 0;
    top: 42px;
    z-index: 20;
  }
  
  /* Пункти меню */
  .dropdown-item {
    padding: 10px 22px;
    font-size: 15px;
    color: #232335;
    background: none;
    border: none;
    width: 100%;
    text-align: left;
    cursor: pointer;
    display: flex;
    align-items: center;
    transition: background 0.15s;
  }
  
  .dropdown-item:hover {
    background: #f6f6fa;
  }
  
  .dropdown-item.text-danger {
    color: #d62424;
  }
  
  </style>
  