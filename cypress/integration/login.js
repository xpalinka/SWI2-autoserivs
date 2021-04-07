describe('Login Test', () => {
  it('Visits login page', () => {
    cy.visit('https://akela.mendelu.cz/~xpalinka/SWI2-autoservis/SWI2-autoserivs/Adam-slim/public/login')
    cy.contains('Login')
    cy.contains('Heslo')
  })
})

