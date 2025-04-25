import React, { useEffect, useState } from 'react';
import { usePage } from '@inertiajs/react';
import { toast } from 'react-hot-toast';
import axios from 'axios';

const CalculNotification = () => {
    const { success, error, info, operationId } = usePage().props;
    const [isCalculating, setIsCalculating] = useState(false);
    const [operationStId, setOperationStId] = useState(null);
    const [retryCount, setRetryCount] = useState(0);
    const MAX_RETRIES = 5; // 50 secondes maximum (5 secondes * 10)

    useEffect(() => {
        // Réinitialiser l'état si on reçoit un nouveau message
        if (info || success || error) {
            setIsCalculating(false);
            setRetryCount(0);
        }

        if (info) {
            setIsCalculating(true);
            setOperationStId(operationId);
            toast.loading(info, {
                id: 'calcul-notification',
                duration: Infinity,
            });

            // Démarrer la vérification périodique
            const interval = setInterval(async () => {
                if (!isCalculating) {
                    clearInterval(interval);
                    return;
                }

                try {
                    const response = await axios.get(`/transmission/${operationStId}/calcul-status`);
                    const { status, message } = response.data;

                    console.log('Status:', status, message, "MAU");

                    if (status !== 'pending') {
                        clearInterval(interval);
                        setIsCalculating(false);
                        setRetryCount(0);

                        if (status === 'success') {
                            toast.success(message, {
                                id: 'calcul-notification',
                            });
                        } else {
                            toast.error(message, {
                                id: 'calcul-notification',
                                duration: 15000,
                            });
                        }
                    } else {
                        setRetryCount(prev => prev + 1);
                        if (retryCount >= MAX_RETRIES) {
                            clearInterval(interval);
                            setIsCalculating(false);
                            toast.error('Le calcul prend trop de temps. Veuillez vérifier les logs pour plus de détails.', {
                                id: 'calcul-notification',
                                duration: 15000,
                            });
                        }
                    }
                } catch (error) {
                    
                    console.error('Erreur lors de la vérification du statut:', error);
                    clearInterval(interval);
                    setIsCalculating(false);
                    
                    const errorMessage = error.response?.data?.message || error.message;
                    toast.error(`Erreur lors de la vérification du statut: ${errorMessage}`, {
                        id: 'calcul-notification',
                        duration: 15000,
                    });
                }
            }, 20000);

            return () => {
                clearInterval(interval);
                setIsCalculating(false);
            };
        }

        if (success) {
            setIsCalculating(false);
            toast.success(success, {
                id: 'calcul-notification',
            });
        }

        if (error) {
            setIsCalculating(false);
            toast.error(error, {
                id: 'calcul-notification',
                duration: 15000,
            });
        }
    }, [info, success, error, operationId, retryCount, operationStId, isCalculating]);

    return null;
};

export default CalculNotification; 