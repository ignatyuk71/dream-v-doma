<template>
  <div class="filters mb-4 card" style="border-radius:5px;">
    <table class="table align-middle category-table-modern">
      <thead>
        <tr>
          <th>Category</th>
          <th>Description</th>
          <th>Parent</th>
          <th>Status</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="cat in categories" :key="cat.id">
          <td>
            <div class="d-flex align-items-center cat-modern-cell">
              <span class="modern-dot"></span>
              <div>
                <div class="product-name">
                  {{ getTranslation(cat, 'name', 'uk') }}
                </div>
              </div>
            </div>
          </td>

          <td>
            <span class="text-muted">
              {{ getTranslation(cat, 'meta_title', 'uk') }}
            </span>
          </td>

          <!-- Parent select -->
          <td>
            <select
              v-model="cat._pendingParent"
              @change="confirmChangeParent(cat)"
              class="form-select"
              style="min-width:140px;"
              :disabled="cat._savingParent"
            >
              <option :value="null">— Коренева категорія —</option>
              <option
                v-for="catOption in availableParents(cat)"
                :key="catOption.id"
                :value="toModelParent(catOption.id)"
              >
                {{ getTranslation(catOption, 'name', 'uk') }}
              </option>
            </select>
            <span v-if="cat._savingParent" class="spinner-border spinner-border-sm ms-1"></span>
          </td>
          <!-- /Parent select -->

          <td>
            <div class="d-flex align-items-center gap-2">
              <div
                :class="['toggle-switch', { active: cat.status == 1 || cat.status === true }]"
                @click="toggleStatus(cat)"
                style="cursor: pointer;"
                title="Змінити статус"
              ></div>
              <span :class="['status-badge', statusClass(cat.status)]">
                {{ statusLabel(cat.status) }}
              </span>
              <span v-if="cat._savingStatus" class="spinner-border spinner-border-sm ms-1"></span>
            </div>
          </td>

          <td class="actions-cell">
            <a
              :href="`/admin/categories/${cat.id}/edit`"
              class="edit-btn text-dark"
              title="Редагувати"
            >
              <i class="bi bi-pencil-square"></i>
            </a>

            <button
              class="dots-menu"
              @click="openMenu(cat.id)"
              type="button"
              title="Ще дії"
            >
              <i class="bi bi-three-dots-vertical"></i>
            </button>

            <div
              v-if="menuOpen === cat.id"
              class="dropdown-menu show"
              @mouseleave="closeMenu"
            >
              <a class="dropdown-item" @click.prevent="download(cat)">
                <i class="bi bi-download me-2"></i> Download
              </a>
              <a
                class="dropdown-item text-danger"
                @click.prevent="deleteCategory(cat)"
              >
                <i class="bi bi-trash me-2"></i> Delete
              </a>
              <a class="dropdown-item" @click.prevent="duplicate(cat)">
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
  name: 'CategoryTable',
  props: {
    categories: { type: Array, required: true }
  },
  data() {
    return { menuOpen: null }
  },
  methods: {
    // нормалізація parent_id
    toModelParent(v) {
      if (v === null || v === undefined || v === '' || v === 0 || v === '0') return null
      const n = Number(v)
      return Number.isNaN(n) ? null : n
    },

    availableParents(current) {
      // не дозволяємо вибирати самого себе
      return this.categories.filter(cat => cat.id !== current.id)
    },

    getTranslation(cat, field = 'name', locale = 'uk') {
      const tr = cat?.translations?.find(t => t.locale === locale)
      return tr ? (tr[field] ?? '') : ''
    },

    confirmChangeParent(cat) {
      const normalizedCurrent = this.toModelParent(cat.parent_id)
      const normalizedPending = this.toModelParent(cat._pendingParent)
      if (normalizedPending === normalizedCurrent) return

      if (confirm('Ви дійсно хочете змінити батьківську категорію?')) {
        this.saveParent(cat)
      } else {
        cat._pendingParent = normalizedCurrent
      }
    },

    async saveParent(cat) {
      cat._savingParent = true
      try {
        await axios.post(`/api/categories/${cat.id}/update-parent`, {
          parent_id: this.toModelParent(cat._pendingParent)
        })
        cat.parent_id = this.toModelParent(cat._pendingParent)
        cat._savingParent = false
        this.$emit('reload')
      } catch (err) {
        cat._pendingParent = this.toModelParent(cat.parent_id)
        cat._savingParent = false
        alert('Помилка при зміні батьківської категорії')
      }
    },

    async toggleStatus(cat) {
      if (cat._savingStatus) return
      const oldStatus = cat.status
      cat.status = (oldStatus == 1 || oldStatus === true) ? 0 : 1
      cat._savingStatus = true
      try {
        await axios.post(`/api/categories/${cat.id}/toggle-status`, { status: cat.status })
        cat._savingStatus = false
        this.$emit('reload')
      } catch {
        cat.status = oldStatus
        cat._savingStatus = false
        alert('Помилка збереження статусу')
      }
    },

    statusLabel(status) {
      if (status == 1 || status === true) return 'Опубліковано'
      if (status == 0 || status === false) return 'Неактивний'
      return ''
    },
    statusClass(status) {
      if (status == 1 || status === true) return 'status-publish'
      if (status == 0 || status === false) return 'status-inactive'
      return ''
    },

    openMenu(idx) {
      if (this.menuOpen === idx) this.closeMenu()
      else {
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

    async deleteCategory(cat) {
      this.closeMenu()
      if (!confirm('Підтвердіть видалення категорії.')) return
      try {
        await axios.delete(`/admin/categories/${cat.id}`)
        alert('Категорія успішно видалена')
        this.$emit('reload')
      } catch {
        alert('Помилка при видаленні категорії')
      }
    },

    download(cat) {
      this.closeMenu()
      alert(`Download category #${cat.id}`)
    },

    duplicate(cat) {
      this.closeMenu()
      this.$emit('duplicate', cat)
    },

    initPendingParentFields() {
      this.categories.forEach(cat => {
        Object.assign(cat, {
          _pendingParent: this.toModelParent(cat.parent_id),
          _savingParent: false,
          _savingStatus: false
        })
      })
    }
  },
  mounted() {
    this.initPendingParentFields()
  },
  watch: {
    categories: {
      handler() {
        this.initPendingParentFields()
      },
      deep: true,
      immediate: true
    }
  }
}
</script>


<style scoped>
.category-table-modern .cat-modern-cell {
  position: relative;
  min-height: 34px;
  gap: 8px;
}
.category-table-modern .modern-dot {
  display: inline-block;
  width: 11px;
  height: 11px;
  background: #6f7ce7;
  border-radius: 50%;
  margin-right: 4px;
  position: relative;
}
.category-table-modern .modern-dot.parent {
  background: #3063ff;
}
.category-table-modern .modern-arrow {
  display: inline-block;
  font-size: 16px;
  color: #b6bbd5;
  margin-right: 7px;
  margin-left: 1px;
  font-weight: bold;
  opacity: 0.8;
  transform: translateY(-1px);
}

.dropdown-menu.show {
  position: fixed !important;
  top: auto !important;
  bottom: auto !important;
  z-index: 2000 !important;
  max-height: none !important;
  overflow: visible !important;
  transform: translate(calc(100% - 180px), 90px) !important;
}

.toggle-switch {
  width: 38px;
  height: 20px;
  background-color: #e5e7eb;
  border-radius: 999px;
  position: relative;
  transition: all 0.3s ease;
  cursor: pointer;
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
  background-color: #22c55e;
}
.toggle-switch.active::before {
  transform: translateX(18px);
}

.table {
  width: 100%;
  border-collapse: collapse;
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
  border-bottom: none;
}

.product-name {
  font-weight: 600;
  color: #111827;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 300px;
}

.text-muted {
  color: #6b7280 !important;
  font-size: 13px;
}

.d-flex { display: flex !important; }
.align-items-center { align-items: center !important; }
.gap-2 { gap: 0.5rem !important; }

.status-badge {
  padding: 4px 12px;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 500;
  display: inline-block;
  white-space: nowrap;
}
.status-publish { background-color: #e9fdf0; color: #22c55e; }
.status-inactive { background-color: #ffecec; color: #ef4444; }

.actions-cell {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 10px;
  min-width: 140px;
}

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
.edit-btn:hover { background: #f2f2f5; color: #1f2937; }

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
.dropdown-item:hover { background: #f6f6fa; }
.dropdown-item.text-danger { color: #d62424; }
</style>
