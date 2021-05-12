describe('Login Test', () => {
  it('Visits login page', () => {
    cy.visit('/login')
    cy.contains(/Email|Login/)
    cy.get('input').first().type('xotradov@mendelu.cz')
    cy.contains('Heslo')
    cy.get('input').last().type('HESLO')
    cy.get('form').submit()
    cy.get('Chyba prihlaseni').should('not.exist');
    cy.get('div').should('not.have.class','alert')
    cy.get('#navbarNav').find('ul').find('li').last().click()
    cy.visit('/login')
    cy.contains('Heslo')
  })
})

