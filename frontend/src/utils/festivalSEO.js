// Indian Festivals and SEO Keywords System
export const indianFestivals = {
  2024: [
    {
      name: "Diwali",
      startDate: "2024-10-31",
      endDate: "2024-11-04",
      keywords: ["diwali sofa cleaning", "festival sofa repair", "diwali home decoration", "sofa deep cleaning diwali", "pre-diwali sofa service", "diwali furniture care"],
      metaTitle: "Diwali Sofa Cleaning & Repair Services | Khushi Home Sofa Repair",
      metaDescription: "Get your sofa ready for Diwali celebrations! Professional sofa cleaning, repair & upholstery services. Book now for sparkling clean sofas this festival season.",
      heroText: "Make Your Sofa Diwali-Ready!",
      promoText: "Special Diwali Offer - 20% Off All Sofa Cleaning Services"
    },
    {
      name: "Holi",
      startDate: "2024-03-08",
      endDate: "2024-03-10",
      keywords: ["post-holi sofa cleaning", "color stain removal", "holi sofa protection", "fabric cleaning after holi", "sofa color stain service"],
      metaTitle: "Post-Holi Sofa Cleaning Services | Color Stain Removal | Khushi Home",
      metaDescription: "Expert post-Holi sofa cleaning services. Remove stubborn color stains and restore your sofa's original beauty. Professional stain removal guaranteed.",
      heroText: "Post-Holi Sofa Rescue Service",
      promoText: "Holi Special - Free Fabric Protection with Every Cleaning"
    },
    {
      name: "Karva Chauth",
      startDate: "2024-11-01",
      endDate: "2024-11-01",
      keywords: ["karva chauth sofa cleaning", "festival preparation", "home decoration sofa care", "special occasion furniture"],
      metaTitle: "Karva Chauth Home Preparation | Sofa Cleaning Services",
      metaDescription: "Prepare your home for Karva Chauth with professional sofa cleaning services. Make your living space perfect for the special celebration.",
      heroText: "Perfect Your Home for Karva Chauth",
      promoText: "Karva Chauth Special - Same Day Service Available"
    },
    {
      name: "Dussehra",
      startDate: "2024-10-12",
      endDate: "2024-10-12",
      keywords: ["dussehra sofa cleaning", "festival home preparation", "sofa deep cleaning service", "pre-festival furniture care"],
      metaTitle: "Dussehra Sofa Cleaning Services | Festival Home Preparation",
      metaDescription: "Celebrate Dussehra with a clean, fresh home. Professional sofa cleaning and repair services to make your celebration memorable.",
      heroText: "Celebrate Dussehra with Fresh, Clean Sofas",
      promoText: "Dussehra Discount - Book Today for Festival-Ready Furniture"
    },
    {
      name: "Christmas",
      startDate: "2024-12-20",
      endDate: "2024-12-26",
      keywords: ["christmas sofa cleaning", "holiday furniture care", "year end deep cleaning", "christmas home preparation"],
      metaTitle: "Christmas Sofa Cleaning & Home Preparation Services",
      metaDescription: "Make your home Christmas-ready with professional sofa cleaning services. Perfect for holiday gatherings and celebrations.",
      heroText: "Get Christmas-Ready with Sparkling Clean Sofas",
      promoText: "Christmas Special - Gift Cards Available for Loved Ones"
    }
  ],
  2025: [
    {
      name: "Makar Sankranti",
      startDate: "2025-01-14",
      endDate: "2025-01-14",
      keywords: ["makar sankranti cleaning", "winter sofa care", "new year furniture service", "sankranti home preparation"],
      metaTitle: "Makar Sankranti Home Preparation | Sofa Cleaning Services",
      metaDescription: "Welcome the harvest festival with clean, fresh sofas. Professional cleaning services for your Makar Sankranti celebrations.",
      heroText: "Fresh Start with Makar Sankranti Sofa Care",
      promoText: "Sankranti Special - New Year, New Look for Your Sofa"
    },
    {
      name: "Holi",
      startDate: "2025-03-14",
      endDate: "2025-03-16",
      keywords: ["pre-holi sofa protection", "post-holi cleaning", "color stain removal", "holi sofa care", "fabric protection holi"],
      metaTitle: "Holi Sofa Care Services | Pre & Post Holi Cleaning | Khushi Home",
      metaDescription: "Protect your sofa for Holi or clean it after! Expert color stain removal and fabric protection services for the festival of colors.",
      heroText: "Holi-Proof Your Sofa or Clean It After!",
      promoText: "Holi Package - Pre-Protection + Post-Cleaning at Special Price"
    },
    {
      name: "Diwali",
      startDate: "2025-10-20",
      endDate: "2025-10-24",
      keywords: ["diwali 2025 sofa cleaning", "festival sofa repair", "deepavali home preparation", "diwali furniture care", "rangoli ready homes"],
      metaTitle: "Diwali 2025 Sofa Cleaning Services | Festival Home Preparation",
      metaDescription: "Make your home Diwali-ready with professional sofa cleaning and repair services. Celebrate the festival of lights with pristine furniture.",
      heroText: "Light Up Your Home with Clean Sofas this Diwali",
      promoText: "Diwali 2025 Special - Complete Home Furniture Care Package"
    },
    {
      name: "Dussehra",
      startDate: "2025-10-02",
      endDate: "2025-10-02",
      keywords: ["dussehra 2025 cleaning", "navratri sofa care", "festival season preparation", "durga puja furniture cleaning"],
      metaTitle: "Dussehra & Navratri Sofa Cleaning Services 2025",
      metaDescription: "Celebrate Dussehra and Navratri with spotless furniture. Professional sofa cleaning services for the festive season.",
      heroText: "Victory Over Dirt - Dussehra Sofa Cleaning",
      promoText: "Navratri-Dussehra Combo Offer - Book Both Festivals Together"
    }
  ]
};

export const defaultSEOKeywords = [
  "sofa repair services",
  "furniture repair",
  "upholstery cleaning",
  "sofa cleaning services",
  "fabric restoration",
  "cushion replacement",
  "furniture restoration",
  "home furniture care",
  "professional sofa repair",
  "furniture maintenance"
];

export const getCurrentFestival = () => {
  const today = new Date();
  const currentYear = today.getFullYear();
  const todayStr = today.toISOString().split('T')[0];
  
  const festivals = indianFestivals[currentYear] || [];
  
  return festivals.find(festival => {
    return todayStr >= festival.startDate && todayStr <= festival.endDate;
  });
};

export const getUpcomingFestival = (daysAhead = 30) => {
  const today = new Date();
  const futureDate = new Date(today.getTime() + (daysAhead * 24 * 60 * 60 * 1000));
  const currentYear = today.getFullYear();
  const todayStr = today.toISOString().split('T')[0];
  const futureDateStr = futureDate.toISOString().split('T')[0];
  
  const festivals = indianFestivals[currentYear] || [];
  
  return festivals.find(festival => {
    return festival.startDate >= todayStr && festival.startDate <= futureDateStr;
  });
};

export const getFestivalSEOData = () => {
  const currentFestival = getCurrentFestival();
  const upcomingFestival = getUpcomingFestival();
  
  if (currentFestival) {
    return {
      type: 'current',
      festival: currentFestival,
      keywords: currentFestival.keywords,
      metaTitle: currentFestival.metaTitle,
      metaDescription: currentFestival.metaDescription,
      heroText: currentFestival.heroText,
      promoText: currentFestival.promoText
    };
  }
  
  if (upcomingFestival) {
    return {
      type: 'upcoming',
      festival: upcomingFestival,
      keywords: [...upcomingFestival.keywords, ...defaultSEOKeywords].slice(0, 10),
      metaTitle: upcomingFestival.metaTitle,
      metaDescription: upcomingFestival.metaDescription,
      heroText: upcomingFestival.heroText,
      promoText: upcomingFestival.promoText
    };
  }
  
  return {
    type: 'default',
    festival: null,
    keywords: defaultSEOKeywords,
    metaTitle: "Professional Sofa Repair & Cleaning Services | Khushi Home Sofa Repair",
    metaDescription: "Expert sofa repair, cleaning, and upholstery services. Professional furniture restoration, fabric care, and cushion replacement. Book your service today!",
    heroText: "Transform Your Sofa with Expert Care",
    promoText: "Quality Furniture Care Services - Book Your Appointment Today"
  };
};