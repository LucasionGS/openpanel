/** @type {import('tailwindcss').Config} */
module.exports = {
  mode: 'jit',
  content: [
    "./web/**/*.php",
  ],
  theme: {
    extend: {
      // https://coolors.co/0b2027-40798c-cccccc-f2f2f2-912f56
      colors: {
        'black': { // rich_black
          DEFAULT: '#0b2027',
          100: '#020708',
          200: '#040d10',
          300: '#071418',
          400: '#091a20',
          500: '#0b2027',
          600: '#1f5b6f',
          700: '#3496b7',
          800: '#70bdd7',
          900: '#b7deeb'
        },
        'blue': { // cerulean
          DEFAULT: '#40798c',
          100: '#0d181c',
          200: '#1a3038',
          300: '#274954',
          400: '#336170',
          500: '#40798c',
          600: '#579bb2',
          700: '#81b4c5',
          800: '#abcdd8',
          900: '#d5e6ec'
        },
        'silver': { // silver
          DEFAULT: '#cccccc',
          100: '#292929',
          200: '#525252',
          300: '#7a7a7a',
          400: '#a3a3a3',
          500: '#cccccc',
          600: '#d6d6d6',
          700: '#e0e0e0',
          800: '#ebebeb',
          900: '#f5f5f5'
        },
        'white': { // white_smoke
          DEFAULT: '#f2f2f2',
          100: '#303030',
          200: '#616161',
          300: '#919191',
          400: '#c2c2c2',
          500: '#f2f2f2',
          600: '#f5f5f5',
          700: '#f7f7f7',
          800: '#fafafa',
          900: '#fcfcfc'
        },
        'magenta': { // quinacridone_magenta
          DEFAULT: '#912f56',
          100: '#1d0911',
          200: '#3b1323',
          300: '#581c34',
          400: '#752646',
          500: '#912f56',
          600: '#c14074',
          700: '#d17096',
          800: '#e0a0b9',
          900: '#f0cfdc'
        }
      },
    },
  },
  plugins: [
    {
      'postcss-import': {},
      'tailwindcss/nesting': {},
      'tailwindcss': {},
    }
  ],
  // safelist: [
  //   "text-black",
  //   "text-blue",
  //   "text-silver",
  //   "text-white",
  //   "text-magenta",
  // ]
}

