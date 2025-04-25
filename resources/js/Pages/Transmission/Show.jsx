import React, { useEffect, useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import {
    Box,
    Typography,
    Paper,
    Grid,
    Button,
    Divider,
    Chip,
    CircularProgress,
    Snackbar,
    Alert,
    Dialog,
    DialogTitle,
    DialogContent,
    DialogActions,
    Table,
    TableBody,
    TableCell,
    TableContainer,
    TableHead,
    TableRow,
    IconButton,
    Tooltip,
} from '@mui/material';
import ArrowBackIcon from '@mui/icons-material/ArrowBack';
import EditIcon from '@mui/icons-material/Edit';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';
import CancelIcon from '@mui/icons-material/Cancel';
import Send from '@mui/icons-material/Send';
import CalculateIcon from '@mui/icons-material/Calculate';
import PrintIcon from '@mui/icons-material/Print';
import { ArrowBack, DateRange } from '@mui/icons-material';
import dayjs from 'dayjs';
import TransmissionProgress from '@/Components/TransmissionProgress';
import DownloadIcon from '@mui/icons-material/Download';

export default function Show({ transmission, auth, success, error }) {
    const [isLoading, setIsLoading] = useState(false);
    const [isLoadingEdit, setIsLoadingEdit] = useState(false);
    const [isLoadingValidate, setIsLoadingValidate] = useState(false);
    const [isLoadingReject, setIsLoadingReject] = useState(false);
    const [isLoadingTransmission, setIsLoadingTransmission] = useState(false);
    const [isLoadingCalculate, setIsLoadingCalculate] = useState(null);
    const [isLoadingPrint, setIsLoadingPrint] = useState(null);

    const [showConfirmation, setShowConfirmation] = useState(false);

    const handleTransmission = () => {
        setShowConfirmation(true);
    };

    const handleConfirmTransmission = () => {
        setIsLoadingTransmission(true);
        setShowConfirmation(false);

        router.get(route('transmission.transmit', transmission.id), {}, {
            onSuccess: () => {
                setIsLoadingTransmission(false);
            },
            onError: (errors) => {
                setIsLoadingTransmission(false);
            }
        });
    };

    const handleCalculate = (rubrique) => {
        setIsLoadingCalculate(rubrique.id);
        router.get(route('transmission.calculate', { transmission: transmission.id, rubrique: rubrique.id }), {
            onSuccess: () => {
                setIsLoadingCalculate(null);
            },
            onError: (errors) => {
                setIsLoadingCalculate(null);
            }
        });
    };

    const handlePrint = (rubrique) => {
        setIsLoadingPrint(rubrique.id);
        router.get(route('transmission.print', { transmission: transmission.id, rubrique: rubrique.id }), {
            onSuccess: () => {
                setIsLoadingPrint(null);
            },
            onError: (errors) => {
                setIsLoadingPrint(null);
            }
        });
    };

    const handleValidate = () => {
        setIsLoadingValidate(true);
        router.get(route('transmission.validate', transmission.id), {
            onSuccess: () => {
                setIsLoadingValidate(false);
                setSnackbar({
                    open: true,
                    message: 'Opération validée avec succès',
                    severity: 'success'
                });
            },
            onError: (errors) => {
                setIsLoadingValidate(false);
                setSnackbar({
                    open: true,
                    message: 'Une erreur est survenue lors de la validation',
                    severity: 'error'
                });
            }
        });
    };

    const handleReject = () => {
        setIsLoadingReject(true);
        router.get(route('transmission.reject', transmission.id), {
            onSuccess: () => {
                setIsLoadingReject(false);
            },
            onError: (errors) => {
                setIsLoadingReject(false);
            }
        });
    };

    return (
        <AdminLayout
            user={auth.user}
            success={success}
            error={error}
            header={
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
                    <Link href={route('transmission.index')}>
                        <Button
                            variant="outlined"
                            startIcon={<ArrowBack />}
                        >
                            Retour
                        </Button>
                    </Link>
                    <Typography variant="h5" component="h1">
                        Transmission <DateRange /> {dayjs(transmission.date_arretee).format('YYYY-MM-DD')}
                    </Typography>
                </Box>
            }
        >
            <Head title={`Transmission #${dayjs(transmission.date_arretee).format('YYYY-MM-DD')}`} />

            <Box sx={{ display: 'flex', justifyContent: 'flex-end', mb: 3 }}>
                <Box sx={{ display: 'flex', gap: 2 }}>
                    {/* {(transmission.etat === 'en_attente' && (auth.isDeclarant || auth.isAdmin)) && (
                        <Link href={route('transmission.edit', transmission.id)}>
                            <Button
                                variant="contained"
                                startIcon={<EditIcon />}
                                disabled={isLoadingEdit}
                            >
                                Modifier
                            </Button>
                        </Link>
                    )} */}
                    {(transmission.etat === 'en_attente' && (auth.isValideur || auth.isAdmin)) && (
                        <Button
                            variant="contained"
                            color="success"
                            startIcon={isLoadingValidate ? <CircularProgress size={20} color="inherit" /> : <CheckCircleIcon />}
                            disabled={isLoadingValidate}
                            onClick={handleValidate}
                        >
                            {isLoadingValidate ? 'Validation en cours...' : 'Valider'}
                        </Button>
                    )}
                    
                    {(transmission.etat === 'en_attente' && (auth.isValideur || auth.isAdmin)) && (
                        <Button
                            variant="contained"
                            color="error"
                            startIcon={isLoadingReject ? <CircularProgress size={20} color="inherit" /> : <CancelIcon />}
                            disabled={isLoadingReject}
                            onClick={handleReject}
                        >
                            {isLoadingReject ? 'Rejet en cours...' : 'Rejeter'}
                        </Button>
                    )}
                    {(transmission.etat === 'validee' && (auth.isDeclarant || auth.isAdmin)) && (
                        <Button
                            variant="contained"
                            color="success"
                            startIcon={isLoadingTransmission ? <CircularProgress size={20} color="inherit" /> : <Send />}
                            onClick={handleTransmission}
                            disabled={transmission.etat === 'transmise' || isLoadingTransmission}
                        >
                            {isLoadingTransmission ? 'Transmission en cours...' : 'Transmettre'}
                        </Button>
                    )}
                    {(auth.isValideur || auth.isAdmin) && (
                        <Button
                            variant="contained"
                            color="primary"
                            startIcon={<DownloadIcon />}
                            onClick={() => window.location.href = route('transmission.download', transmission.id)}
                        >
                            Télécharger le fichier
                        </Button>
                    )}
                </Box>
            </Box>

            <Paper sx={{ p: 3, mb: 3 }}>
                <Grid container spacing={3}>
                    <Grid item xs={12}>
                        <Typography variant="h6" gutterBottom>
                            Informations générales
                        </Typography>
                        <Divider sx={{ mb: 2 }} />
                        <Grid container spacing={2}>
                            <Grid item xs={12} sm={6}>
                                <Typography variant="subtitle2" color="text.secondary">
                                    Date d'arrêt
                                </Typography>
                                <Typography variant="body1">
                                    {dayjs(transmission.date_arretee).format('YYYY-MM-DD')}
                                </Typography>
                            </Grid>
                            <Grid item xs={12} sm={6}>
                                <Typography variant="subtitle2" color="text.secondary">
                                    Etat
                                </Typography>
                                <Chip
                                    label={transmission.etat === "en_attente" ? "En attente" : transmission.etat === "validee" ? "Validée" : transmission.etat === "rejetee" ? "Rejetée" : "Transmise"}
                                    color={
                                        transmission.etat === 'validee'
                                            ? 'success'
                                            : transmission.etat === 'rejetee'
                                                ? 'error'
                                                : transmission.etat === 'transmise'
                                                    ? 'success'
                                                : 'warning'
                                    }
                                />
                            </Grid>
                        </Grid>
                    </Grid>

                    <Grid item xs={12}>
                        <Typography variant="h6" gutterBottom>
                            Personnes concernées
                        </Typography>
                        <Divider sx={{ mb: 2 }} />
                        <Grid container spacing={2}>
                            <Grid item xs={12} sm={6}>
                                <Typography variant="subtitle2" color="text.secondary">
                                    Déclarant
                                </Typography>
                                <Typography variant="body1">
                                    {transmission.declarant?.name}
                                </Typography>
                            </Grid>
                            <Grid item xs={12} sm={6}>
                                <Typography variant="subtitle2" color="text.secondary">
                                    Valideur
                                </Typography>
                                <Typography variant="body1">
                                    {transmission.valideur?.name || '-'}
                                </Typography>
                            </Grid>
                        </Grid>
                    </Grid>
                    
                </Grid>
            </Paper>

            {/* Tableau des opérations */}
            {
                (transmission.etat === "transmise" && ( auth.isDeclarant || auth.isAdmin))
                &&
                <Paper sx={{ p: 3 }}>
                    <Typography variant="h6" gutterBottom>
                        Opérations
                    </Typography>
                    <Divider sx={{ mb: 2 }} />
                    <TableContainer>
                        <Table>
                            <TableHead>
                                <TableRow>
                                    <TableCell>Rubrique</TableCell>
                                    <TableCell>Code</TableCell>
                                    <TableCell>Description</TableCell>
                                    <TableCell>État</TableCell>
                                    <TableCell align="right">Actions</TableCell>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {transmission.rubrique?.children?.map((rubrique) => (
                                    <TableRow key={rubrique.id}>
                                        <TableCell>{rubrique?.nom}</TableCell>
                                        <TableCell>{rubrique?.code}</TableCell>
                                        <TableCell>{rubrique?.description}</TableCell>
                                        <TableCell>
                                            <Chip
                                                label={rubrique.etat}
                                                color={
                                                    rubrique.etat === 'validee'
                                                        ? 'success'
                                                        : rubrique.etat === 'rejetee'
                                                            ? 'error'
                                                            : 'warning'
                                                }
                                                size="small"
                                            />
                                        </TableCell>
                                        <TableCell align="right">
                                            <Box sx={{ display: 'flex', justifyContent: 'flex-end', gap: 1 }}>
                                                {(auth.isDeclarant || auth.isAdmin) && <Tooltip title="Calculer">
                                                    <IconButton
                                                        color="primary"
                                                        onClick={() => handleCalculate(rubrique)}
                                                        disabled={!rubrique.actif || isLoadingCalculate === rubrique.id}
                                                    >
                                                        {isLoadingCalculate === rubrique.id ? <CircularProgress size={24} /> : <CalculateIcon />}
                                                    </IconButton>
                                                </Tooltip>
                                                }
                                                {(auth.isDeclarant || auth.isAdmin) && <Tooltip title="Imprimer">
                                                    <IconButton
                                                        color="secondary"
                                                        onClick={() => handlePrint(rubrique)}
                                                        disabled
                                                        //disabled={!rubrique.actif || isLoadingPrint === rubrique.id}
                                                    >
                                                        {isLoadingPrint === rubrique.id ? <CircularProgress size={24} /> : <PrintIcon />}
                                                    </IconButton>
                                                </Tooltip>
                                                }
                                            </Box>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </TableContainer>
                </Paper>
            }


            {/* Dialog de confirmation */}
            <Dialog
                open={showConfirmation}
                onClose={() => setShowConfirmation(false)}
            >
                <DialogTitle>Confirmer la transmission</DialogTitle>
                <DialogContent>
                    <Typography>
                        Êtes-vous sûr de vouloir transmettre cette transmission ?
                        Cette action est irréversible.
                    </Typography>
                </DialogContent>
                <DialogActions>
                    <Button onClick={() => setShowConfirmation(false)}>
                        Annuler
                    </Button>
                    <Button
                        onClick={handleConfirmTransmission}
                        color="success"
                        variant="contained"
                        disabled={isLoading}
                    >
                        {isLoading ? (
                            <>
                                <CircularProgress size={20} sx={{ mr: 1 }} />
                                Transmission en cours...
                            </>
                        ) : (
                            'Confirmer'
                        )}
                    </Button>
                </DialogActions>
            </Dialog>
        </AdminLayout>
    );
} 