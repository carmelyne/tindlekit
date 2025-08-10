/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class',
  content: [
    './*.html',
    './*.js',
    './**/*.php'
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['ui-sans-serif', 'system-ui', 'Inter', 'Arial', 'sans-serif']
      },
      colors: {
        tk: {
          bg: '#0b1020',
          card: '#11162a',
          border: '#24324a',
          fg: '#e6edf3',
          muted: '#9fb3c8',
          accent: '#22d3ee',
          'accent-start': '#06b6d4',
          success: '#22c55e'
        }
      },
      backgroundImage: {
        'tindlekit-gradient': 'radial-gradient(1000px 600px at 50% -20%, #14203a, #0b1020)'
      },
      keyframes: {
        'fade-in': {
          '0%': { opacity: '0', transform: 'translateY(12px)' },
          '100%': { opacity: '1', transform: 'translateY(0px)' }
        },
        'slide-in': {
          '0%': { opacity: '0', transform: 'translateY(16px)' },
          '100%': { opacity: '1', transform: 'translateY(0px)' }
        },
        'card-hover': {
          '0%': { transform: 'translateY(0px) scale(1)' },
          '100%': { transform: 'translateY(-2px) scale(1.002)' }
        }
      },
      animation: {
        'fade-in': 'fade-in 0.4s cubic-bezier(0.16, 1, 0.3, 1)',
        'slide-in': 'slide-in 0.3s cubic-bezier(0.16, 1, 0.3, 1)',
        'card-hover': 'card-hover 0.2s cubic-bezier(0.16, 1, 0.3, 1)'
      }
    }
  },
  plugins: []
}
