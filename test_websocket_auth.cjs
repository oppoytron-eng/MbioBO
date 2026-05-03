const WebSocket = require('ws');
const axios = require('axios');

async function testWebSocketWithAuth() {
    try {
        // 1. Se connecter en tant que client pour obtenir un token
        console.log('1. Authentification client...');
        const loginResponse = await axios.post('http://localhost:8000/api/login', {
            email: 'test@test.com',
            password: 'password'
        });

        const token = loginResponse.data.token;
        console.log('Token obtenu:', token.substring(0, 30) + '...');

        // 2. Se connecter à Reverb
        console.log('2. Connexion à Reverb...');
        const ws = new WebSocket('ws://localhost:8080/app/jok7ds3jzeq09vremxld');

        let socketId = null;

        ws.on('open', function open() {
            console.log('Connecté à Reverb');
        });

        ws.on('message', async function message(data) {
            const parsed = JSON.parse(data);
            console.log('Message reçu:', parsed);
            
            if (parsed.event === 'pusher:connection_established') {
                const connectionData = JSON.parse(parsed.data);
                socketId = connectionData.socket_id;
                console.log('Socket ID:', socketId);
                
                // 3. S'authentifier pour le channel privé
                console.log('3. Authentification channel privé...');
                try {
                    const authResponse = await axios.post('http://localhost:8000/api/broadcasting/auth', {
                        socket_id: socketId,
                        channel_name: 'private-course.59'
                    }, {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/x-www-form-urlencoded'
                        }
                    });

                    const authData = authResponse.data;
                    console.log('Auth response:', authData);

                    // 4. S'abonner au channel
                    const subscribeMessage = {
                        event: 'pusher:subscribe',
                        data: {
                            channel: 'private-course.59',
                            auth: authData.auth
                        }
                    };
                    
                    ws.send(JSON.stringify(subscribeMessage));
                    console.log('Abonnement envoyé');

                } catch (error) {
                    console.error('Erreur auth channel:', error.response?.data || error.message);
                }
            }
            
            if (parsed.event === 'driver.location.updated') {
                console.log('🎉 Position du chauffeur mise à jour:', parsed.data);
            }
        });

        ws.on('error', function error(err) {
            console.error('Erreur WebSocket:', err);
        });

        ws.on('close', function close() {
            console.log('Déconnecté de Reverb');
        });

        // 5. Envoyer un événement de test après 2 secondes
        setTimeout(async () => {
            console.log('5. Envoi d\'un événement de test...');
            try {
                await axios.post('http://localhost:8000/api/courses/59/tracking', {
                    latitude: 3.8500,
                    longitude: 11.5040,
                    bearing: 50.0,
                    speed: 30.0
                }, {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    }
                });
                console.log('Événement de test envoyé');
            } catch (error) {
                console.error('Erreur envoi événement:', error.response?.data || error.message);
            }
        }, 2000);

    } catch (error) {
        console.error('Erreur générale:', error.message);
    }
}

testWebSocketWithAuth();
