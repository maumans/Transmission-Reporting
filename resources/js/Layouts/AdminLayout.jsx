import React, { useState, useEffect } from 'react';
import { Link, usePage, router } from '@inertiajs/react';
import { ThemeProvider, createTheme } from '@mui/material/styles';
import CssBaseline from '@mui/material/CssBaseline';
import {
    Box,
    Drawer,
    AppBar,
    Toolbar,
    List,
    Typography,
    Divider,
    IconButton,
    ListItem,
    ListItemButton,
    ListItemIcon,
    ListItemText,
    Container,
    Avatar,
    Menu,
    MenuItem,
    Collapse,
    Snackbar,
    Alert,
} from '@mui/material';
import {
    Menu as MenuIcon,
    ChevronLeft as ChevronLeftIcon,
    Dashboard as DashboardIcon,
    People as PeopleIcon,
    Settings as SettingsIcon,
    AccountCircle,
    Description as DescriptionIcon,
    Assessment as AssessmentIcon,
    ExpandLess,
    ExpandMore,
} from '@mui/icons-material';
import { styled } from '@mui/material/styles';
import { Toaster } from 'react-hot-toast';
import CalculNotification from '@/Components/Notifications/CalculNotification';

const drawerWidth = 240;

const Main = styled('main', { shouldForwardProp: (prop) => prop !== 'open' })(
    ({ theme, open }) => ({
        flexGrow: 1,
        padding: theme.spacing(3),
        transition: theme.transitions.create('margin', {
            easing: theme.transitions.easing.sharp,
            duration: theme.transitions.duration.leavingScreen,
        }),
        marginLeft: `-${drawerWidth}px`,
        ...(open && {
            transition: theme.transitions.create('margin', {
                easing: theme.transitions.easing.easeOut,
                duration: theme.transitions.duration.enteringScreen,
            }),
            marginLeft: 0,
        }),
    }),
);

const AppBarStyled = styled(AppBar, {
    shouldForwardProp: (prop) => prop !== 'open',
})(({ theme, open }) => ({
    transition: theme.transitions.create(['margin', 'width'], {
        easing: theme.transitions.easing.sharp,
        duration: theme.transitions.duration.leavingScreen,
    }),
    ...(open && {
        width: `calc(100% - ${drawerWidth}px)`,
        marginLeft: `${drawerWidth}px`,
        transition: theme.transitions.create(['margin', 'width'], {
            easing: theme.transitions.easing.easeOut,
            duration: theme.transitions.duration.enteringScreen,
        }),
    }),
}));

const DrawerHeader = styled('div')(({ theme }) => ({
    display: 'flex',
    alignItems: 'center',
    padding: theme.spacing(0, 1),
    ...theme.mixins.toolbar,
    justifyContent: 'flex-end',
}));

const theme = createTheme({
    palette: {
        primary: {
            main: '#1976d2',
        },
        secondary: {
            main: '#dc004e',
        },
        background: {
            default: '#f5f5f5',
            paper: '#ffffff',
        },
    },
    components: {
        MuiDrawer: {
            styleOverrides: {
                paper: {
                    backgroundColor: '#1976d2',
                    color: 'white',
                },
            },
        },
        MuiListItemButton: {
            styleOverrides: {
                root: {
                    '&.Mui-selected': {
                        backgroundColor: 'rgba(255, 255, 255, 0.1)',
                    },
                    '&:hover': {
                        backgroundColor: 'rgba(255, 255, 255, 0.08)',
                    },
                },
            },
        },
        MuiListItemIcon: {
            styleOverrides: {
                root: {
                    color: 'white',
                    minWidth: '40px',
                },
            },
        },
        MuiListItemText: {
            styleOverrides: {
                primary: {
                    color: 'white',
                },
            },
        },
    },
});

export default function AdminLayout({ children, header }) {
    const [open, setOpen] = useState(true);
    const [anchorEl, setAnchorEl] = useState(null);
    const user = usePage().props.auth.user;
    const isAdmin = usePage().props.auth.isAdmin;
    const { success, error, info } = usePage().props;
    const [openSubmission, setOpenSubmission] = useState(false);
    const [openReport, setOpenReport] = useState(false);
    const [openParametre, setOpenParametre] = useState(false);

    const [snackbar, setSnackbar] = useState({
        open: false,
        message: '',
        severity: 'success'
    });

    useEffect(() => {
        if (success) {
            setSnackbar({
                open: true,
                message: success,
                severity: 'success'
            });
        }
        if (error) {
            setSnackbar({
                open: true,
                message: error,
                severity: 'error'
            });
        }
        if (info) {
            setSnackbar({
                open: true,
                message: info,
                severity: 'info'
            });
        }
    }, [success, error, info]);

    const handleCloseSnackbar = () => {
        setSnackbar(prev => ({ ...prev, open: false }));
    };

    const handleDrawerOpen = () => {
        setOpen(true);
    };

    const handleDrawerClose = () => {
        setOpen(false);
    };

    const handleMenu = (event) => {
        setAnchorEl(event.currentTarget);
    };

    const handleClose = () => {
        setAnchorEl(null);
    };

    return (
        <ThemeProvider theme={theme}>
            <CssBaseline />
            <Toaster position="top-right" />
            <CalculNotification />
            <Box sx={{
                display: 'flex',
                minHeight: '100vh',
                position: 'relative',
                '& .inertia-progress': {
                    position: 'fixed',
                    top: 0,
                    left: 0,
                    right: 0,
                    zIndex: 10
                }
            }}>
                <AppBarStyled position="fixed" open={open} sx={{ zIndex: 10 }}>
                    <Toolbar>
                        <IconButton
                            color="inherit"
                            aria-label="open drawer"
                            onClick={handleDrawerOpen}
                            edge="start"
                            sx={{ mr: 2, ...(open && { display: 'none' }) }}
                        >
                            <MenuIcon />
                        </IconButton>
                        <Typography variant="h6" noWrap component="div" sx={{ flexGrow: 1 }}>
                            Transmission Reporting
                        </Typography>
                        <button
                           
                            onClick={handleMenu}
                            color="inherit"
                        >
                            {user.profile_photo_url ? (
                                <div className='flex gap-2'>
                                    <div className='text-lg'>
                                        {user.name}
                                    </div>
                                    <Avatar
                                        src={user.profile_photo_url}
                                        alt={user.name}
                                        sx={{ width: 32, height: 32 }}
                                    />
                                </div>
                            ) : (
                                <div className='flex gap-2'>
                                    <div className='text-lg'>
                                        {user.name}
                                    </div>
                                    <AccountCircle />
                                </div>
                            )}
                        </button>
                        <Menu
                            id="menu-appbar"
                            anchorEl={anchorEl}
                            anchorOrigin={{
                                vertical: 'bottom',
                                horizontal: 'right',
                            }}
                            keepMounted
                            transformOrigin={{
                                vertical: 'top',
                                horizontal: 'right',
                            }}
                            open={Boolean(anchorEl)}
                            onClose={handleClose}
                        >
                            <MenuItem component={Link} href={route('profile.edit')} onClick={handleClose}>
                                Profil
                            </MenuItem>
                            <MenuItem
                                component={Link}
                                href={route('logout')}
                                method="post"
                                as="button"
                                onClick={(e) => {
                                    e.preventDefault();
                                    router.post(route('logout'));
                                    handleClose();
                                }}
                            >
                                Déconnexion
                            </MenuItem>
                        </Menu>
                    </Toolbar>
                </AppBarStyled>
                <Drawer
                    sx={{
                        width: drawerWidth,
                        flexShrink: 0,
                        '& .MuiDrawer-paper': {
                            width: drawerWidth,
                            boxSizing: 'border-box',
                            zIndex: 11
                        },
                    }}
                    variant="persistent"
                    anchor="left"
                    open={open}
                >
                    <DrawerHeader>
                        <IconButton onClick={handleDrawerClose}>
                            <ChevronLeftIcon sx={{ color: 'white' }} />
                        </IconButton>
                    </DrawerHeader>
                    <Divider />
                    <List>
                        <ListItem disablePadding>
                            <ListItemButton component={Link} href={route('dashboard')}>
                                <ListItemIcon>
                                    <DashboardIcon />
                                </ListItemIcon>
                                <ListItemText primary="Dashboard" />
                            </ListItemButton>
                        </ListItem>
                        <ListItem disablePadding>
                            <ListItemButton component={Link} href={route('transmission.index')}>
                                <ListItemIcon>
                                    <DashboardIcon />
                                </ListItemIcon>
                                <ListItemText primary="Transmission" />
                            </ListItemButton>
                        </ListItem>
                        {/* <ListItem disablePadding>
                            <ListItemButton onClick={() => setOpenReport(!openReport)}>
                                <ListItemIcon>
                                    <AssessmentIcon />
                                </ListItemIcon>
                                <ListItemText primary="Rapport" />
                                {openReport ? <ExpandLess /> : <ExpandMore />}
                            </ListItemButton>
                        </ListItem>
                        <Collapse in={openReport} timeout="auto" unmountOnExit>
                            <List component="div" disablePadding>
                                <ListItemButton sx={{ pl: 4 }} component={Link} href={route('balance.index')}>
                                    <ListItemIcon>
                                        <AssessmentIcon />
                                    </ListItemIcon>
                                    <ListItemText primary="Balance" />
                                </ListItemButton>
                                <ListItemButton sx={{ pl: 4 }} component={Link} href={route('annexe.index')}>
                                    <ListItemIcon>
                                        <AssessmentIcon />
                                    </ListItemIcon>
                                    <ListItemText primary="Annexe" />
                                </ListItemButton>
                            </List>
                        </Collapse> */}

                        {
                            (isAdmin) && (
                                <>
                                    <ListItem disablePadding>
                                        <ListItemButton onClick={() => setOpenParametre(!openParametre)}>
                                            <ListItemIcon>
                                                <DescriptionIcon />
                                            </ListItemIcon>
                                            <ListItemText primary="Paramètres" />
                                            {openParametre ? <ExpandLess /> : <ExpandMore />}
                                        </ListItemButton>
                                    </ListItem>
                                    <Collapse in={openParametre} timeout="auto" unmountOnExit>
                                        <List component="div" disablePadding>
                                            <ListItemButton sx={{ pl: 4 }} component={Link} href={route('user.index')}>
                                                <ListItemIcon>
                                                    <DescriptionIcon />
                                                </ListItemIcon>
                                                <ListItemText primary="Utilisateurs" />
                                            </ListItemButton>
                                        </List>
                                    </Collapse>
                                </>
                            )
                        }

                    </List>
                </Drawer>
                <Main open={open} sx={{ position: 'relative', zIndex: 1 }}>
                    <DrawerHeader />
                    <Container maxWidth="lg" sx={{ mt: 4, mb: 4 }}>
                        <Box className="mb-5">
                            {header}
                        </Box>
                        {children}
                    </Container>
                </Main>
            </Box>
            <Snackbar
                open={snackbar.open}
                autoHideDuration={6000}
                onClose={handleCloseSnackbar}
                anchorOrigin={{ vertical: 'bottom', horizontal: 'left' }}
                sx={{
                    zIndex: 9999,
                    '& .MuiAlert-root': {
                        zIndex: 9999
                    }
                }}
            >
                <Alert
                    onClose={handleCloseSnackbar}
                    severity={snackbar.severity}
                    sx={{ width: '100%' }}
                    variant="filled"
                >
                    {snackbar.message}
                </Alert>
            </Snackbar>
        </ThemeProvider>
    );
} 