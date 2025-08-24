<template>
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
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

              <!-- Product -->
              <td>
                <div class="d-flex align-items-center">
                  <img :src="product.image" class="avatar me-3"/>
                  <div class="min-w-0">
                    <div class="fw-medium text-truncate">{{ product.name }}</div>
                    <small class="text-muted text-truncate">{{ product.brand }}</small>
                  </div>
                </div>
              </td>

              <!-- Category -->
              <td>{{ product.category_name }}</td>

              <!-- Stock (toggle) -->
              <td>
                <div
                  :class="['toggle-switch', { active: product.status == 1 }]"
                  @click="toggleStatus(product)"
                  title="Змінити статус"
                ></div>
              </td>

              <!-- SKU -->
              <td>{{ product.sku }}</td>

              <!-- Price -->
              <td>{{ product.price }}</td>

              <!-- Qty -->
              <td>{{ product.qty }}</td>

              <!-- Status -->
              <td>
                <span :class="['badge', statusClass(product.status)]">
                  {{ statusLabel(product.status) }}
                </span>
              </td>

              <!-- Actions -->
              <td class="text-end">
                <div class="dropdown">
                  <button
                    type="button"
                    class="btn p-0 dropdown-toggle hide-arrow"
                    data-bs-toggle="dropdown"
                  >
                    <i class="bi bi-three-dots-vertical"></i>
                  </button>
                  <div class="dropdown-menu dropdown-menu-end">
                    <a class="dropdown-item" :href="`/admin/products/${product.id}/edit`">
                      <i class="bi bi-pencil me-1"></i> Edit
                    </a>
                    <a class="dropdown-item" href="javascript:void(0);" @click.prevent="duplicate(product)">
                      <i class="bi bi-files me-1"></i> Duplicate
                    </a>
                    <hr class="dropdown-divider"/>
                    <a class="dropdown-item text-danger" href="javascript:void(0);" @click.prevent="deleteProduct(product)">
                      <i class="bi bi-trash me-1"></i> Delete
                    </a>
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
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





  
<style scoped>


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

.toggle-switch.active::before {
  transform: translateX(18px);
}
  
Ї.table th {
    color: #6b7280;
    font-weight: 600;
    text-transform: uppercase;
    background: #f9fafb;
  }

  
  /* Аватар */
  .avatar {
    width: 50px;
    height: 50px;
    border-radius: 8px;
    object-fit: cover;
    margin-right: 12px;
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
  

  
  .toggle-switch.active {
    background-color: #6366f1;
  }
  
  .toggle-switch.active::before {
    transform: translateX(18px);
  }
  
  
  </style>
  