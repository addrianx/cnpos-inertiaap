import { ref, computed } from 'vue'
import { useProductUtils } from './useProductUtils.js'

export function useAiRecommender(components) {
  const { getFinalPrice, getHasStock, getDiscount, getTotalStock } = useProductUtils()
  
  // AI Builder State
  const aiBudget = ref(0)
  const selectedUseCase = ref('')
  const aiRecommendations = ref([])
  const aiLoading = ref(false)

// ✅ PERBAIKAN: AI Templates dengan kategori database
  const aiTemplates = {
    gaming: {
      name: '🎮 Gaming PC',
      description: 'Optimized for gaming performance',
      priority: ['VGA Card', 'Processor', 'RAM', 'Motherboard'],
      budgetAllocation: {
        'VGA Card': 0.35,
        'Processor': 0.25,
        'Motherboard': 0.15,
        'RAM': 0.10,
        'SSD': 0.08,
        'Power Supply': 0.05,
        'Casing': 0.02
      }
    },
    office: {
      name: '💼 Office PC', 
      description: 'Optimal untuk produktivitas kerja',
      priority: ['Processor', 'RAM', 'SSD', 'Motherboard'],
      budgetAllocation: {
        'Processor': 0.30,
        'RAM': 0.20,
        'SSD': 0.15,
        'Motherboard': 0.15,
        'Power Supply': 0.10,
        'Casing': 0.08,
        'VGA Card': 0.02
      }
    },
    editing: {
      name: '🎬 Content Creation',
      description: 'Didesain untuk video/photo editing',
      priority: ['Processor', 'RAM', 'VGA Card', 'SSD'],
      budgetAllocation: {
        'Processor': 0.28,
        'RAM': 0.22,
        'VGA Card': 0.20,
        'SSD': 0.12,
        'Motherboard': 0.10,
        'Power Supply': 0.06,
        'Casing': 0.02
      }
    },
    budget: {
      name: '💰 Budget Build',
      description: 'Maksimalkan performa dengan budget terbatas',
      priority: ['Processor', 'Motherboard', 'RAM', 'SSD'],
      budgetAllocation: {
        'Processor': 0.25,
        'Motherboard': 0.20,
        'RAM': 0.18,
        'SSD': 0.15,
        'Power Supply': 0.12,
        'Casing': 0.08,
        'VGA Card': 0.02
      }
    }
  }

  // Computed Properties
  const canGenerateAI = computed(() => {
    return aiBudget.value >= 1000000 && selectedUseCase.value !== ''
  })

  const aiBuildSummary = computed(() => {
    if (aiRecommendations.value.length === 0) return null

    const totalCost = aiRecommendations.value.reduce((sum, rec) => {
      return sum + (getFinalPrice(rec.recommendedProduct) || 0)
    }, 0)

    const componentsCount = aiRecommendations.value.filter(rec => rec.recommendedProduct).length

    return {
      totalCost,
      remainingBudget: aiBudget.value - totalCost,
      componentsCount,
      efficiency: Math.round((totalCost / aiBudget.value) * 100)
    }
  })

  // Methods
  const getUseCaseDescription = (useCase) => {
    return aiTemplates[useCase]?.description || ''
  }

 // ✅ PERBAIKAN: Method generateAIRecommendations
  
    const generateAIRecommendations = async () => {
      if (!canGenerateAI.value) return

      aiLoading.value = true
      aiRecommendations.value = []

      // Simulate AI processing
      setTimeout(() => {
        const template = aiTemplates[selectedUseCase.value]
        if (!template) {
          aiLoading.value = false
          return
        }

        const recommendations = []

        // Generate recommendations for each category in template
        Object.entries(template.budgetAllocation).forEach(([category, percentage]) => {
          const availableProducts = components[category] || []
          
          // Skip jika tidak ada produk di kategori ini
          if (availableProducts.length === 0) {
            recommendations.push({
              category,
              budgetPercentage: 0,
              budgetAmount: 0,
              recommendedProduct: null,
              alternatives: [],
              isOptimal: false,
              badgeClass: 'bg-secondary',
              hasProducts: false,
              message: 'Tidak ada produk di kategori ini'
            })
            return
          }

          const categoryBudget = aiBudget.value * percentage
          const recommendedProduct = findBestProductForBudget(availableProducts, categoryBudget)
          const alternatives = findAlternativeProducts(availableProducts, categoryBudget, recommendedProduct?.id)

          recommendations.push({
            category,
            budgetPercentage: Math.round(percentage * 100),
            budgetAmount: categoryBudget,
            recommendedProduct,
            alternatives,
            isOptimal: recommendedProduct && isProductOptimal(recommendedProduct, categoryBudget),
            badgeClass: getPriorityBadgeClass(template.priority.indexOf(category)),
            hasProducts: true
          })
        })

        aiRecommendations.value = recommendations
        aiLoading.value = false
      }, 2000)
    }


  const findBestProductForBudget = (products, targetBudget) => {
    if (!products || products.length === 0) return null

    return products
      .filter(product => getHasStock(product))
      .map(product => ({
        ...product,
        price_diff: Math.abs(getFinalPrice(product) - targetBudget),
        value_score: calculateValueScore(product, targetBudget)
      }))
      .sort((a, b) => {
        if (Math.abs(a.price_diff - b.price_diff) < targetBudget * 0.1) {
          return b.value_score - a.value_score
        }
        return a.price_diff - b.price_diff
      })[0] || null
  }

  const findAlternativeProducts = (products, targetBudget, excludeId) => {
    return products
      .filter(product => product.id !== excludeId && getHasStock(product))
      .map(product => ({
        ...product,
        price_diff: Math.abs(getFinalPrice(product) - targetBudget)
      }))
      .sort((a, b) => a.price_diff - b.price_diff)
      .slice(0, 3)
  }

  const calculateValueScore = (product, targetBudget) => {
    const price = getFinalPrice(product)
    const baseScore = 100 - (Math.abs(price - targetBudget) / targetBudget * 100)
    
    let featureBonus = 0
    if (getDiscount(product) > 0) featureBonus += 10
    if (getTotalStock(product) > 5) featureBonus += 5
    
    return Math.max(0, baseScore + featureBonus)
  }

  const isProductOptimal = (product, targetBudget) => {
    const price = getFinalPrice(product)
    return price >= targetBudget * 0.9 && price <= targetBudget * 1.1
  }

  const getPriorityBadgeClass = (priorityIndex) => {
    if (priorityIndex < 2) return 'bg-danger'
    if (priorityIndex < 4) return 'bg-warning text-dark'
    return 'bg-info'
  }

  const clearAIRecommendations = () => {
    aiRecommendations.value = []
    selectedUseCase.value = ''
  }

  return {
    // State
    aiBudget,
    selectedUseCase,
    aiRecommendations,
    aiLoading,
    
    // Computed
    canGenerateAI,
    aiBuildSummary,
    
    // Methods
    getUseCaseDescription,
    generateAIRecommendations,
    clearAIRecommendations,
    aiTemplates
  }
}