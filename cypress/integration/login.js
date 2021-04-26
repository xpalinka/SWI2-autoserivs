describe('Login Test', () => {
  it('Visits login page', () => {
    cy.visit('https://akela.mendelu.cz/~xpalinka/SWI2-autoservis/test/SWI2-autoserivs/Adam-slim/public/login')
    cy.contains('Login')
    cy.get('input').first().type('xotradov@mendelu.cz')
    cy.contains('Heslo')
    cy.get('input').last().type('HESLO')
    cy.get('form').submit()
    cy.get('Chyba prihlaseni').should('not.exist');
    cy.get('div').should('not.have.class','alert')
    cy.get('#nav-item').last().click()
    cy.visit('/')
    cy.contains('Heslo')
  })
})

