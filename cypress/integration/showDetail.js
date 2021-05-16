describe('Details', () => {
  beforeEach(() => {
    cy.visit('/login')
    cy.get('input').first().type('xotradov@mendelu.cz')
    cy.get('input').last().type('HESLO')
    cy.get('form').submit()
  })

  it('Look into detail of protocol and go back', () => {
    cy.visit('/auth/reservations')
    cy.contains('Rezervacie')
    cy.get('.btn').contains('Protokol').click()
    cy.contains("Detail protokolu")
    cy.contains('Email')
    cy.contains('Telefon')
    cy.get('.btn').contains('Zpet').click()
  })

  it('Look into detail of protocol and add work form', () => {
    cy.visit('/auth/reservations')
    cy.contains('Rezervacie')
    cy.get('.btn').contains('Protokol').click()
    cy.get('.btn').contains('Přidat činnost').click()
    cy.contains('Název')
    cy.contains('Cena')
  })

  it('Look into detail of protocol and add work form', () => {
    cy.visit('/auth/reservations')
    cy.contains('Rezervacie')
    cy.get('.btn').contains('Protokol').click()
    cy.get('#mytable').find('th').eq(3).contains('Pridať')
    cy.get('#mytable').find('tr').eq(1).find('td').eq(3).click()
    cy.get('.btn').contains('Zpet').click()
  })
})

