describe('Basic Tests', () => {
  beforeEach(() => {
    cy.visit('/login')
    cy.get('input').first().type('xotradov@mendelu.cz')
    cy.get('input').last().type('HESLO')
    cy.get('form').submit()
  })

  it('Walk through pages', () => {
    cy.visit('/auth/protocols')
    cy.contains('Protokoly')
    cy.visit('/auth/reservations')
    cy.contains('Rezervacie')
  })
})

