describe('Login Test', () => {
  it('Visits login page', () => {
    cy.visit('https://akela.mendelu.cz/~xpalinka/SWI2-autoservis/test/SWI2-autoserivs/Adam-slim/public/login')
    cy.contains('Login')
    cy.get('input').type('xotradov@mendelu.cz')
    cy.contains('Heslo')
    cy.get('input').type('HESLO')
    cy.get('form').submit()
    cy.get('Odhlasit').click()
    cy.visit('/')
    cy.contains('Heslo')
  })
})

