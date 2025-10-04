import { ref, computed } from 'vue'
import { useProductUtils } from './useProductUtils.js'

export function usePcBuilder() {
  const { getFinalPrice, getHasStock, getDiscount, getTotalStock, getDescription } = useProductUtils()
  
  // State
  const selectedComponents = ref([])
  const assemblyName = ref('')
  const assemblyDescription = ref('')

  // Computed
  const totalPrice = computed(() => {
    return selectedComponents.value.reduce((total, component) => {
      return total + getFinalPrice(component)
    }, 0)
  })

  const canSave = computed(() => {
    return selectedComponents.value.length > 0 && assemblyName.value.trim() !== ''
  })

  // Methods
  const isSelected = (product) => {
    return selectedComponents.value.some(comp => comp.id === product.id)
  }

  const toggleComponent = (product) => {
    if (!getHasStock(product)) {
      return false
    }

    const existingIndex = selectedComponents.value.findIndex(comp => comp.id === product.id)
    
    if (existingIndex > -1) {
      selectedComponents.value.splice(existingIndex, 1)
      return 'removed'
    } else {
      const sameCategoryIndex = selectedComponents.value.findIndex(
        comp => comp.category?.name === product.category?.name
      )
      
      if (sameCategoryIndex > -1) {
        selectedComponents.value.splice(sameCategoryIndex, 1)
      }
      
      selectedComponents.value.push(product)
      return 'added'
    }
  }

  const removeComponent = (productId) => {
    selectedComponents.value = selectedComponents.value.filter(comp => comp.id !== productId)
  }

  const addComponent = (product) => {
    if (!getHasStock(product)) {
      return false
    }

    // Remove existing product in same category
    const sameCategoryIndex = selectedComponents.value.findIndex(
      comp => comp.category?.name === product.category?.name
    )
    
    if (sameCategoryIndex > -1) {
      selectedComponents.value.splice(sameCategoryIndex, 1)
    }
    
    selectedComponents.value.push(product)
    return true
  }

  const addMultipleComponents = (products) => {
    // Clear existing selection
    selectedComponents.value = []
    
    // Add all products
    products.forEach(product => {
      addComponent(product)
    })
  }

  const clearAssembly = () => {
    selectedComponents.value = []
    assemblyName.value = ''
    assemblyDescription.value = ''
  }

  return {
    // State
    selectedComponents,
    assemblyName,
    assemblyDescription,
    
    // Computed
    totalPrice,
    canSave,
    
    // Methods
    isSelected,
    toggleComponent,
    removeComponent,
    addComponent,
    addMultipleComponents,
    clearAssembly,
    getDescription
  }
}