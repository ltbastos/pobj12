import html2pdf from 'html2pdf.js'
import type { Ref } from 'vue'

export function usePDFExport() {
  const exportToPDF = async (elementRef: HTMLElement | Ref<HTMLElement | null> | string, filename?: string) => {
    let element: HTMLElement | null = null
    
    if (typeof elementRef === 'string') {
      element = document.querySelector(`#${elementRef}`) as HTMLElement
    } else if (elementRef instanceof HTMLElement) {
      element = elementRef
    } else if (elementRef && 'value' in elementRef) {
      element = elementRef.value
    }
    
    if (!element) {
      console.error(`Elemento não encontrado`)
      throw new Error(`Elemento não encontrado`)
    }

    const defaultFilename = filename || `visao-executiva-${new Date().toISOString().split('T')[0]}.pdf`

    const opt = {
      margin: [10, 10, 10, 10] as [number, number, number, number],
      filename: defaultFilename,
      image: { type: 'jpeg' as const, quality: 0.98 },
      html2canvas: { 
        scale: 2,
        useCORS: true,
        logging: false,
        backgroundColor: '#ffffff'
      },
      jsPDF: { 
        unit: 'mm', 
        format: 'a4', 
        orientation: 'landscape' as const
      },
      pagebreak: { mode: ['avoid-all', 'css', 'legacy'] }
    }

    try {
      await html2pdf().set(opt).from(element).save()
    } catch (error) {
      console.error('Erro ao gerar PDF:', error)
      throw error
    }
  }

  return {
    exportToPDF
  }
}
