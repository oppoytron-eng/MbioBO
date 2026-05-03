const WebSocket = require('ws');

// Connexion au serveur Reverb
const ws = new WebSocket('ws://localhost:8080/app/jok7ds3jzeq09vremxld');

ws.on('open', function open() {
    console.log('Connecté à Reverb');
    
    // S'authentifier et s'abonner au channel privé
    const authMessage = {
        event: 'pusher:subscribe',
        data: {
            channel: 'private-course.59',
            auth: null // Sera rempli par l'authentification Laravel
        }
    };
    
    ws.send(JSON.stringify(authMessage));
});

ws.on('message', function message(data) {
    const parsed = JSON.parse(data);
    console.log('Message reçu:', parsed);
    
    if (parsed.event === 'driver.location.updated') {
        console.log('Position du chauffeur mise à jour:', parsed.data);
    }
});

ws.on('error', function error(err) {
    console.error('Erreur WebSocket:', err);
});

ws.on('close', function close() {
    console.log('Déconnecté de Reverb');
});
