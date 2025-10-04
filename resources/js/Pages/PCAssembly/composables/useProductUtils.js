// composables/useProductUtils.js
import { ref, computed } from 'vue'

export function useProductUtils() {
  const formatPrice = (price) => {
    const numPrice = parseFloat(price)
    if (isNaN(numPrice)) {
      return '0'
    }
    return new Intl.NumberFormat('id-ID').format(numPrice)
  }

  const getFormattedPrice = (product) => {
    if (product.formatted_price !== undefined) return product.formatted_price
    return formatPrice(product.price || 0)
  }

  const getFinalPrice = (product) => {
    if (!product) return 0
    if (product.final_price !== undefined) return product.final_price
    
    let price = parseFloat(product.price || 0)
    const discount = getDiscount(product)
    
    if (discount > 0) {
      const discountAmount = price * (discount / 100)
      price = price - discountAmount
    }
    
    return price
  }

  const getDiscount = (product) => {
    return parseFloat(product.discount || 0)
  }

  const getTotalStock = (product) => {
    if (!product) return 0
    if (product.total_stock !== undefined) return product.total_stock
    if (product.stock !== undefined) return product.stock
    if (product.stocks && product.stocks.length > 0) {
      return product.stocks.reduce((sum, stock) => sum + parseInt(stock.quantity || 0), 0)
    }
    return 0
  }

  const getHasStock = (product) => {
    if (!product) return false
    if (product.has_stock !== undefined) return product.has_stock
    return getTotalStock(product) > 0
  }

  const getDescription = (product) => {
    if (product.description !== undefined) return product.description
    return `SKU: ${product.sku || 'N/A'}`
  }

  // ✅ PERBAIKAN: Format nama kategori untuk display (dari format AI ke format user-friendly)
  const formatCategoryName = (category) => {
    const names = {
      'processors': 'Processor',
      'motherboards': 'Motherboard',
      'memories': 'Memory/RAM',
      'graphics_cards': 'Graphics Card',
      'storages': 'Storage',
      'power_supplies': 'Power Supply',
      'cases': 'Case',
      'cpu_coolers': 'CPU Cooler'
    }
    return names[category] || category
  }

  return {
    formatPrice,
    getFormattedPrice,
    getFinalPrice,
    getDiscount,
    getTotalStock,
    getHasStock,
    getDescription,
    formatCategoryName
  }
}