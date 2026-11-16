import { images } from '@/constants/images'

export interface ProductCategory {
  slug: string
  title: string
  icon: string
  image: string        // ruta imagen; '' = sin imagen (TODO)
  description: string
  examples: string[]
  brands: string[]     // TODO confirmar marcas con el dueño
  ctaMessage: string   // mensaje WhatsApp precargado (encodeURIComponent)
  enabled: boolean     // false = no renderizar (ej. reacondicionados pendiente de confirmar)
}

export const productCategories: ProductCategory[] = [
  {
    slug: 'portatiles',
    title: 'Portátiles',
    icon: 'IconLaptop',
    image: images.imageLaptopRepairReal,
    description: 'Portátiles para todos los usos: desde el trabajo diario hasta el gaming más exigente.',
    examples: ['Portátiles domésticos', 'Portátiles profesionales', 'Gaming laptops', 'Ultrabooks'],
    brands: [], // TODO confirmar marcas disponibles (ej. HP, Lenovo, Asus, Acer, MSI…)
    ctaMessage: 'Hola%2C%20me%20gustar%C3%ADa%20consultar%20disponibilidad%20de%20Port%C3%A1tiles',
    enabled: true,
  },
  {
    slug: 'sobremesa',
    title: 'Sobremesa',
    icon: 'IconDesktop',
    image: images.imagePcRepairReal,
    description: 'Equipos de sobremesa de marca y montajes a medida adaptados a tu presupuesto y necesidades.',
    examples: ['Equipos de marca', 'Montajes a medida', 'Mini PCs', 'All-in-one'],
    brands: [], // TODO confirmar marcas disponibles
    ctaMessage: 'Hola%2C%20me%20gustar%C3%ADa%20consultar%20disponibilidad%20de%20Sobremesa',
    enabled: true,
  },
  {
    slug: 'componentes',
    title: 'Componentes',
    icon: 'IconElectronic',
    image: images.imageMotherboard,
    description: 'Todo lo que necesitas para montar o mejorar tu equipo: procesadores, gráficas, RAM y más.',
    examples: ['Procesadores', 'Tarjetas gráficas', 'Memorias RAM', 'Placas base', 'Fuentes de alimentación', 'Cajas y refrigeración'],
    brands: [], // TODO confirmar marcas disponibles (ej. Intel, AMD, Nvidia, Kingston, Corsair…)
    ctaMessage: 'Hola%2C%20me%20gustar%C3%ADa%20consultar%20disponibilidad%20de%20Componentes',
    enabled: true,
  },
  {
    slug: 'accesorios',
    title: 'Accesorios',
    icon: 'IconCable',
    image: images.imageShopReal,
    description: 'Periféricos y accesorios para completar tu setup: teclados, ratones, auriculares y cables.',
    examples: ['Teclados y ratones', 'Auriculares y headsets', 'Cables y adaptadores', 'Cargadores', 'Fundas y protectores', 'Hubs USB'],
    brands: [], // TODO confirmar marcas disponibles
    ctaMessage: 'Hola%2C%20me%20gustar%C3%ADa%20consultar%20disponibilidad%20de%20Accesorios',
    enabled: true,
  },
  {
    slug: 'impresoras',
    title: 'Impresoras',
    icon: 'IconPrinter',
    image: images.imagePrinterRepairReal,
    description: 'Impresoras de tinta, láser y multifunción para el hogar y la oficina.',
    examples: ['Impresoras de tinta', 'Impresoras láser', 'Multifunción', 'Impresoras fotográficas'],
    brands: [], // TODO confirmar marcas disponibles (ej. HP, Canon, Epson, Brother…)
    ctaMessage: 'Hola%2C%20me%20gustar%C3%ADa%20consultar%20disponibilidad%20de%20Impresoras',
    enabled: true,
  },
  {
    slug: 'consumibles',
    title: 'Consumibles',
    icon: 'IconCartridge',
    image: '', // TODO añadir imagen de consumibles
    description: 'Cartuchos, tóner y papel compatibles y originales para mantener tu impresora al día.',
    examples: ['Cartuchos de tinta', 'Tóner láser', 'Papel fotográfico', 'Papel de impresión'],
    brands: [], // TODO confirmar marcas disponibles
    ctaMessage: 'Hola%2C%20me%20gustar%C3%ADa%20consultar%20disponibilidad%20de%20Consumibles',
    enabled: true,
  },
  {
    slug: 'almacenamiento',
    title: 'Almacenamiento',
    icon: 'IconHarddisk',
    image: images.imageMotherboardMobile,
    description: 'Soluciones de almacenamiento para cualquier necesidad: velocidad, capacidad o portabilidad.',
    examples: ['SSDs internos y externos', 'Discos duros HDD', 'Pendrives y tarjetas SD', 'NAS y almacenamiento en red'],
    brands: [], // TODO confirmar marcas disponibles (ej. Samsung, Seagate, WD, Kingston…)
    ctaMessage: 'Hola%2C%20me%20gustar%C3%ADa%20consultar%20disponibilidad%20de%20Almacenamiento',
    enabled: true,
  },
  {
    slug: 'software',
    title: 'Software',
    icon: 'IconSoftware',
    image: '', // TODO añadir imagen de software
    description: 'Licencias originales de antivirus, suites de ofimática y sistemas operativos.',
    examples: ['Antivirus y seguridad', 'Microsoft Office / 365', 'Windows 10 / 11', 'Software profesional'],
    brands: [], // TODO confirmar productos disponibles
    ctaMessage: 'Hola%2C%20me%20gustar%C3%ADa%20consultar%20disponibilidad%20de%20Software',
    enabled: true,
  },
]

// Reacondicionados: desactivado hasta confirmar disponibilidad
export const refurbishedEnabled = false // TODO confirmar disponibilidad de equipos reacondicionados
