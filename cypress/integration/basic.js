describe('Basic Tests', () => {
  beforeEach(() => {
    cy.visit('/login')
    cy.get('input').first().type('xotradov@mendelu.cz')
    cy.get('input').last().type('HESLO')
    cy.get('form').submit()
  })
  
  it('Walk through pages', () => {
    cy.visit('/protocols')
    cy.contains('Protokoly')
    cy.visit('/reservations')
    cy.contains('Rezervacie')
  })
})

